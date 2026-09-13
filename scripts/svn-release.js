#!/usr/bin/env node
/**
 * Stage a release in the WordPress.org SVN working copy, without committing.
 *
 * Usage: npm run svn:release
 *
 * Builds the archive, mirrors it into svn/trunk (adding new files and removing
 * files the new version no longer ships), refreshes svn/assets from
 * .wordpress-org, sets image MIME types, and prints the exact commands to
 * commit and tag.
 *
 * It stops short of committing on purpose. A commit publishes to every site
 * that runs the plugin within minutes, asks for your WordPress.org password,
 * and cannot be quietly undone. That step stays in your hands.
 */

'use strict';

const fs = require( 'fs' );
const os = require( 'os' );
const path = require( 'path' );
const { execFileSync } = require( 'child_process' );

const SLUG = 'dicebar';
const root = path.resolve( __dirname, '..' );
const svnRoot = path.join( root, 'svn' );
const trunk = path.join( svnRoot, 'trunk' );
const assets = path.join( svnRoot, 'assets' );
const assetSource = path.join( root, '.wordpress-org' );

const MIME = { '.png': 'image/png', '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.gif': 'image/gif', '.svg': 'image/svg+xml' };

/**
 * Run svn with arguments, never through a shell.
 *
 * @param {string[]} args Arguments.
 * @param {Object}   opts Options for execFileSync.
 * @return {string} Output.
 */
const svn = ( args, opts = {} ) =>
	execFileSync( 'svn', args, { cwd: svnRoot, encoding: 'utf8', ...opts } );

/**
 * Every file under a directory, as forward-slash paths relative to it.
 *
 * @param {string} dir  Directory.
 * @param {string} base Prefix for recursion.
 * @return {string[]} Relative paths.
 */
const listFiles = ( dir, base = '' ) => {
	if ( ! fs.existsSync( dir ) ) {
		return [];
	}

	return fs.readdirSync( dir, { withFileTypes: true } ).flatMap( ( entry ) => {
		if ( entry.name === '.svn' ) {
			return [];
		}

		const rel = base ? base + '/' + entry.name : entry.name;

		return entry.isDirectory() ? listFiles( path.join( dir, entry.name ), rel ) : [ rel ];
	} );
};

if ( ! fs.existsSync( path.join( svnRoot, '.svn' ) ) ) {
	process.stderr.write(
		'\nNo SVN working copy at svn/. Check it out first:\n' +
			'  svn checkout https://plugins.svn.wordpress.org/' + SLUG + '/ svn\n'
	);
	process.exit( 1 );
}

// 1. Build, and read the version the build actually carries.
execFileSync( process.execPath, [ path.join( __dirname, 'build-zip.js' ) ], { cwd: root, stdio: 'inherit' } );

const header = fs.readFileSync( path.join( root, SLUG + '.php' ), 'utf8' );
const version = ( header.match( /^\s*\*\s*Version:\s*(\S+)/m ) || [] )[ 1 ];
const stable = ( fs.readFileSync( path.join( root, 'readme.txt' ), 'utf8' ).match( /^Stable tag:\s*(\S+)/m ) || [] )[ 1 ];

if ( ! version || version !== stable ) {
	process.stderr.write( '\nRefusing to stage: header Version ' + version + ' and readme Stable tag ' + stable + ' disagree.\n' );
	process.exit( 1 );
}

if ( fs.existsSync( path.join( svnRoot, 'tags', version ) ) ) {
	process.stderr.write( '\nRefusing to stage: tags/' + version + ' already exists. Bump the version first.\n' );
	process.exit( 1 );
}

// 2. Unpack the archive to a scratch directory. The archive is the source of
//    truth, so trunk only ever holds what users actually receive.
const scratch = fs.mkdtempSync( path.join( os.tmpdir(), 'dicebar-svn-' ) );

// unzip, not tar: GNU tar (Git Bash, most Linux) cannot read zip archives.
execFileSync( 'unzip', [ '-q', '-o', path.join( root, 'dist', SLUG + '.zip' ), '-d', scratch ] );

const built = path.join( scratch, SLUG );
const builtFiles = new Set( listFiles( built ) );
const trunkFiles = listFiles( trunk );

// 3. Files the new version no longer ships have to be removed from SVN, or
//    they linger in trunk and ship forever.
const removed = trunkFiles.filter( ( rel ) => ! builtFiles.has( rel ) );

for ( const rel of removed ) {
	svn( [ 'rm', '--force', '--quiet', path.join( 'trunk', rel ) ] );
}

// 4. Copy the new build over trunk.
for ( const rel of builtFiles ) {
	const target = path.join( trunk, rel );

	fs.mkdirSync( path.dirname( target ), { recursive: true } );
	fs.copyFileSync( path.join( built, rel ), target );
}

fs.rmSync( scratch, { recursive: true, force: true } );

// 5. Directory assets: icon, banners and screenshots, never inside trunk.
fs.mkdirSync( assets, { recursive: true } );

const assetFiles = fs
	.readdirSync( assetSource )
	.filter( ( name ) => /^(icon|banner|screenshot)-[\w-]+\.(png|jpe?g|gif)$/i.test( name ) );

for ( const name of assetFiles ) {
	fs.copyFileSync( path.join( assetSource, name ), path.join( assets, name ) );
}

// 6. Schedule additions, then fix image types so the directory serves images
//    rather than downloads.
svn( [ 'add', '--force', '--quiet', 'trunk', 'assets' ] );

for ( const name of assetFiles ) {
	const mime = MIME[ path.extname( name ).toLowerCase() ];

	if ( mime ) {
		svn( [ 'propset', 'svn:mime-type', mime, '--quiet', path.join( 'assets', name ) ] );
	}
}

// 7. Report, and hand over the commit.
const status = svn( [ 'status' ] ).trim();
const counts = {};

for ( const line of status.split( '\n' ).filter( Boolean ) ) {
	counts[ line[ 0 ] ] = ( counts[ line[ 0 ] ] || 0 ) + 1;
}

process.stdout.write( '\nStaged version ' + version + ' in svn/.\n' );
process.stdout.write( '  added: ' + ( counts.A || 0 ) + '   modified: ' + ( counts.M || 0 ) + '   removed: ' + ( counts.D || 0 ) + '\n' );

if ( removed.length ) {
	process.stdout.write( '  no longer shipped: ' + removed.join( ', ' ) + '\n' );
}

process.stdout.write(
	'\nReview with:  cd svn && svn status && svn diff trunk\n' +
		'\nThen commit and tag, from the svn/ directory:\n' +
		'  svn ci -m "Release ' + version + '" --username dicecodes\n' +
		'  svn cp trunk tags/' + version + '\n' +
		'  svn ci -m "Tag ' + version + '" --username dicecodes\n'
);
