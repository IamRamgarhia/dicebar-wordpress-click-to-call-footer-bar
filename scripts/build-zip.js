#!/usr/bin/env node
/**
 * Build the distributable plugin archive into dist/.
 *
 * The archive contains only what a user should receive: one folder, named
 * exactly as the slug, holding everything .distignore does not exclude.
 * Tooling, tests, documentation and sources stay behind.
 *
 * Two things here are load-bearing rather than tidiness.
 *
 * The folder name inside the archive must be the slug. WordPress installs a
 * plugin into a directory named after the archive's top-level folder, and if
 * that name is not the slug then the plugin's own paths, its text domain
 * lookup and its update mechanism all disagree with where it actually lives.
 *
 * The archive is written by scripts/lib/zip.js rather than by a system tool.
 * PowerShell's Compress-Archive writes Windows path separators into entry
 * names, which the ZIP specification forbids, and an archive built that way
 * extracts on Linux as one flat file called "plugin\\file.php". WordPress then
 * reports that the plugin file does not exist, which is true and useless.
 *
 * @package TapBar
 */

'use strict';

const fs = require( 'fs' );
const path = require( 'path' );
const zip = require( './lib/zip' );

const SLUG = 'tapbar-mobile-action-bar';
const root = path.resolve( __dirname, '..' );
const dist = path.join( root, 'dist' );

/**
 * Patterns from .distignore, plus those that are never shippable.
 *
 * @return {string[]} Exclusion patterns.
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
 * A pattern containing an asterisk matches by file name anywhere in the tree,
 * which is how a .distignore entry such as *.md is meant to read.
 *
 * @param {string}   relative Path relative to the project root, slash separated.
 * @param {string[]} excluded Exclusion patterns.
 * @return {boolean} True when the path must not ship.
 */
const isExcluded = ( relative, excluded ) =>
	excluded.some( ( pattern ) => {
		const clean = pattern.replace( /^\//, '' ).replace( /\/$/, '' );

		if ( clean.includes( '*' ) ) {
			const expression = new RegExp(
				'^' + clean.replace( /[.+^${}()|[\]\\]/g, '\\$&' ).replace( /\*/g, '.*' ) + '$'
			);

			return expression.test( path.basename( relative ) ) || expression.test( relative );
		}

		return relative === clean || relative.startsWith( clean + '/' );
	} );

/**
 * Every shippable path, as archive entries.
 *
 * Names are built with forward slashes regardless of the host platform.
 *
 * @param {string}   from     Directory to read.
 * @param {string[]} excluded Exclusion patterns.
 * @param {string}   base     Path relative to the project root.
 * @param {string}   prefix   Path inside the archive.
 * @return {Array} Archive entries.
 */
const collect = ( from, excluded, base, prefix ) => {
	const entries = [];
	const items = fs
		.readdirSync( from, { withFileTypes: true } )
		.sort( ( a, b ) => a.name.localeCompare( b.name ) );

	for ( const item of items ) {
		const relative = base ? base + '/' + item.name : item.name;

		if ( isExcluded( relative, excluded ) ) {
			continue;
		}

		const source = path.join( from, item.name );
		const inArchive = prefix + item.name;

		if ( item.isDirectory() ) {
			entries.push( { name: inArchive + '/', data: null } );
			entries.push( ...collect( source, excluded, relative, inArchive + '/' ) );
		} else if ( item.isFile() ) {
			entries.push( { name: inArchive, data: fs.readFileSync( source ) } );
		}
	}

	return entries;
};

const entries = [ { name: SLUG + '/', data: null } ].concat(
	collect( root, exclusions(), '', SLUG + '/' )
);

const files = entries.filter( ( entry ) => ! entry.name.endsWith( '/' ) );
const mainFile = SLUG + '/' + SLUG + '.php';

// A missing main file produces an archive that installs and then fails with
// "the plugin file does not exist". Failing here says which file is missing.
if ( ! files.some( ( entry ) => entry.name === mainFile ) ) {
	process.stderr.write(
		'\nRefusing to build: the archive has no ' + mainFile + '.\n' +
			'WordPress locates a plugin by that exact path.\n'
	);
	process.exit( 1 );
}

fs.mkdirSync( dist, { recursive: true } );

const archive = path.join( dist, SLUG + '.zip' );

fs.writeFileSync( archive, zip.build( entries ) );

for ( const entry of files ) {
	process.stdout.write( '  ' + entry.name + '\n' );
}

process.stdout.write(
	'\nBuilt ' +
		path.relative( root, archive ) +
		' (' +
		( fs.statSync( archive ).size / 1024 ).toFixed( 1 ) +
		' KB)\n'
);
