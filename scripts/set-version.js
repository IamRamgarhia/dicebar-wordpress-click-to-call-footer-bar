#!/usr/bin/env node
/**
 * Set the plugin version in every place that has to agree.
 *
 * Three files carry the version and all three must match. When the plugin
 * header and the readme's stable tag disagree, WordPress.org serves the tag
 * named by the readme, which means the wrong code ships and nothing warns you.
 * Doing it by hand is how that happens, so it is done here instead.
 *
 * Usage: npm run version:set -- 1.2.0
 */

'use strict';

const fs = require( 'fs' );
const path = require( 'path' );

const root = path.resolve( __dirname, '..' );
const SLUG = 'dicebar';
const version = process.argv[ 2 ];

if ( ! version || ! /^\d+\.\d+\.\d+$/.test( version ) ) {
	process.stderr.write( '\nUsage: npm run version:set -- 1.2.0\n' );
	process.exit( 1 );
}

// Four places carry the version and every one of them must agree. The plugin
// header and the readme's stable tag decide what WordPress.org ships; the
// constant decides the cache-busting query on every asset. A test asserts all
// of them match, which is how the missing fourth entry here was found.
const edits = [
	{
		file: SLUG + '.php',
		find: /^(\s*\*\s*Version:\s*)(\S+)$/m,
		label: 'plugin header',
	},
	{
		file: SLUG + '.php',
		find: /^(define\( 'DICEBAR_VERSION', ')([^']+)(' \);)$/m,
		replace: '$1' + '__VERSION__' + '$3',
		label: 'DICEBAR_VERSION constant',
	},
	{
		file: 'readme.txt',
		find: /^(Stable tag:\s*)(\S+)$/m,
		label: 'readme stable tag',
	},
];

for ( const edit of edits ) {
	const file = path.join( root, edit.file );
	const before = fs.readFileSync( file, 'utf8' );

	if ( ! edit.find.test( before ) ) {
		process.stderr.write( '\nCould not find the version in ' + edit.file + '.\n' );
		process.exit( 1 );
	}

	var replacement = edit.replace
		? edit.replace.replace( '__VERSION__', version )
		: '$1' + version;

	fs.writeFileSync( file, before.replace( edit.find, replacement ) );
	process.stdout.write( '  ' + edit.label + ' -> ' + version + '\n' );
}

const pkgPath = path.join( root, 'package.json' );
const pkg = JSON.parse( fs.readFileSync( pkgPath, 'utf8' ) );

pkg.version = version;
fs.writeFileSync( pkgPath, JSON.stringify( pkg, null, '\t' ) + '\n' );
process.stdout.write( '  package.json -> ' + version + '\n' );

process.stdout.write( '\nAdd a Changelog entry in readme.txt before you build.\n' );
