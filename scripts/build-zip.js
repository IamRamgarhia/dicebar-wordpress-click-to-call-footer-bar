#!/usr/bin/env node
/**
 * Build the distributable plugin archive into dist/.
 *
 * The archive contains only what a user should receive: the plugin folder,
 * named exactly as the slug, holding everything not excluded by .distignore.
 * Tooling, tests, documentation and sources stay behind.
 *
 * The folder name inside the zip matters. WordPress installs a plugin into a
 * directory named after the archive's top-level folder, and that name has to
 * be the slug or the plugin's own paths and the update mechanism disagree
 * with where it actually lives.
 */

'use strict';

const fs = require( 'fs' );
const os = require( 'os' );
const path = require( 'path' );
const { execFileSync } = require( 'child_process' );

const SLUG = 'tapbar-mobile-action-bar';
const root = path.resolve( __dirname, '..' );
const dist = path.join( root, 'dist' );

/**
 * Patterns from .distignore, plus the ones that are never shippable.
 *
 * @return {string[]} Path fragments to exclude.
 */
const exclusions = () => {
	const file = path.join( root, '.distignore' );
	const listed = fs.existsSync( file )
		? fs
				.readFileSync( file, 'utf8' )
				.split( '\n' )
				.map( ( line ) => line.trim() )
				.filter( ( line ) => line && ! line.startsWith( '#' ) )
		: [];

	return listed.concat( [ '.git', '.superpowers', 'dist', '.phpunit.result.cache' ] );
};

/**
 * Whether a path relative to the project root is excluded.
 *
 * @param {string}   relative  Path relative to the project root, slash separated.
 * @param {string[]} excluded  Exclusion fragments.
 * @return {boolean} True when the path should not ship.
 */
const isExcluded = ( relative, excluded ) =>
	excluded.some( ( pattern ) => {
		const clean = pattern.replace( /^\//, '' ).replace( /\/$/, '' );

		// A pattern containing * matches by name anywhere in the tree, which is
		// how .distignore entries such as *.md are meant to read.
		if ( clean.includes( '*' ) ) {
			const expression = new RegExp(
				`^${ clean.replace( /[.+^${}()|[\]\\]/g, '\\$&' ).replace( /\*/g, '.*' ) }$`
			);

			return expression.test( path.basename( relative ) ) || expression.test( relative );
		}

		return relative === clean || relative.startsWith( `${ clean }/` );
	} );

/**
 * Copy the shippable tree into a staging directory.
 *
 * @param {string}   from      Source directory.
 * @param {string}   to        Destination directory.
 * @param {string[]} excluded  Exclusion fragments.
 * @param {string}   base      Path prefix relative to the project root.
 */
const stage = ( from, to, excluded, base = '' ) => {
	fs.mkdirSync( to, { recursive: true } );

	for ( const entry of fs.readdirSync( from, { withFileTypes: true } ) ) {
		const relative = base ? `${ base }/${ entry.name }` : entry.name;

		if ( isExcluded( relative, excluded ) ) {
			continue;
		}

		const source = path.join( from, entry.name );
		const target = path.join( to, entry.name );

		if ( entry.isDirectory() ) {
			stage( source, target, excluded, relative );
		} else if ( entry.isFile() ) {
			fs.copyFileSync( source, target );
		}
	}
};

const staging = fs.mkdtempSync( path.join( os.tmpdir(), 'tbar-build-' ) );
const pluginDir = path.join( staging, SLUG );

stage( root, pluginDir, exclusions() );

fs.mkdirSync( dist, { recursive: true } );

const archive = path.join( dist, `${ SLUG }.zip` );

if ( fs.existsSync( archive ) ) {
	fs.unlinkSync( archive );
}

// PowerShell's Compress-Archive is present on every supported Windows and
// avoids adding a packaging dependency. zip(1) covers macOS and Linux.
if ( process.platform === 'win32' ) {
	execFileSync(
		'powershell',
		[
			'-NoProfile',
			'-Command',
			`Compress-Archive -Path '${ pluginDir }' -DestinationPath '${ archive }' -Force`,
		],
		{ stdio: 'inherit' }
	);
} else {
	execFileSync( 'zip', [ '-r', '-q', archive, SLUG ], { cwd: staging, stdio: 'inherit' } );
}

fs.rmSync( staging, { recursive: true, force: true } );

const shipped = [];

const walk = ( dir, base = '' ) => {
	for ( const entry of fs.readdirSync( dir, { withFileTypes: true } ) ) {
		const relative = base ? `${ base }/${ entry.name }` : entry.name;

		if ( entry.isDirectory() ) {
			walk( path.join( dir, entry.name ), relative );
		} else {
			shipped.push( relative );
		}
	}
};

process.stdout.write( `\nBuilt ${ path.relative( root, archive ) }\n` );
process.stdout.write( `${ ( fs.statSync( archive ).size / 1024 ).toFixed( 1 ) } KB\n` );
