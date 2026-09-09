#!/usr/bin/env node
/**
 * Bring the wp-env environment up reliably, then run the PHP suite.
 *
 * Two failures make a plain `wp-env start` unreliable, and both of them cost
 * real time to diagnose from the raw error:
 *
 * 1. MySQL's container reports "Started" as soon as the process launches, well
 *    before the server accepts connections. wp-env immediately runs a WP-CLI
 *    command against it, which fails with "Connection refused" and aborts the
 *    whole start. Waiting for the server to answer, then starting again, makes
 *    it deterministic. `wp-env start` is idempotent, so the second run simply
 *    finishes the steps the first one could not reach.
 *
 * 2. A start interrupted partway through leaves containers created but not
 *    running. The next start then fails on a name conflict rather than reusing
 *    or replacing them. Removing this project's containers first makes that
 *    unreachable.
 *
 * Exists as a script rather than a shell one-liner because it has to run the
 * same way on Windows, macOS and CI.
 */

'use strict';

const { execSync } = require( 'child_process' );

const MYSQL_TIMEOUT_MS = 180000;
const POLL_INTERVAL_MS = 3000;
const START_ATTEMPTS = 3;

/**
 * Run a command, showing its output, and throw if it fails.
 *
 * @param {string} command Command to run.
 */
const run = ( command ) => {
	process.stdout.write( `\n$ ${ command }\n` );
	execSync( command, { stdio: 'inherit' } );
};

/**
 * Run a command, swallowing failure and returning its trimmed output.
 *
 * @param {string} command Command to run.
 * @return {string} Output, or an empty string when the command failed.
 */
const quiet = ( command ) => {
	try {
		return execSync( command, {
			encoding: 'utf8',
			stdio: [ 'ignore', 'pipe', 'ignore' ],
		} ).trim();
	} catch ( error ) {
		return '';
	}
};

/**
 * Try a command, reporting only whether it succeeded.
 *
 * @param {string} command Command to run.
 * @return {boolean} True when the command exited zero.
 */
const attempt = ( command ) => {
	process.stdout.write( `\n$ ${ command }\n` );

	try {
		execSync( command, { stdio: 'inherit' } );
		return true;
	} catch ( error ) {
		return false;
	}
};

/**
 * Block until a condition holds or the timeout expires.
 *
 * Uses a synchronous sleep because this script is a linear sequence and an
 * async version would buy nothing but complexity.
 *
 * @param {Function} condition Returns true when the wait is over.
 * @param {number}   timeout   Milliseconds to wait before giving up.
 * @return {boolean} True when the condition held before the timeout.
 */
const waitFor = ( condition, timeout ) => {
	const deadline = Date.now() + timeout;

	while ( Date.now() < deadline ) {
		if ( condition() ) {
			return true;
		}

		Atomics.wait( new Int32Array( new SharedArrayBuffer( 4 ) ), 0, 0, POLL_INTERVAL_MS );
		process.stdout.write( '.' );
	}

	return false;
};

/**
 * Container ids belonging to a wp-env environment.
 *
 * wp-env names every container after a 32 character hash of the project's
 * absolute path, so the hash prefix is what identifies them. Matching on that
 * shape avoids touching unrelated containers on the same machine.
 *
 * @return {string[]} Container ids.
 */
const wpEnvContainers = () => {
	// Every id is checked against Docker's own hexadecimal id format before it
	// reaches a command string, so nothing shell-significant can pass through.
	const ids = quiet( 'docker ps -aq' )
		.split( '\n' )
		.filter( ( id ) => /^[0-9a-f]{6,64}$/.test( id.trim() ) )
		.map( ( id ) => id.trim() );

	return ids.filter( ( id ) => {
		const name = quiet( `docker inspect --format "{{.Name}}" ${ id }` );
		return /^\/[0-9a-f]{32}-/.test( name );
	} );
};

/**
 * Whether the environment's MySQL servers answer a ping.
 *
 * @return {boolean} True when every MySQL container answers.
 */
const mysqlIsReady = () => {
	const ids = wpEnvContainers().filter( ( id ) => {
		const name = quiet( `docker inspect --format "{{.Name}}" ${ id }` );
		return name.includes( 'mysql' );
	} );

	if ( ! ids.length ) {
		return false;
	}

	return ids.every( ( id ) => quiet( `docker exec ${ id } mysqladmin ping --silent` ) !== '' ||
		quiet( `docker exec ${ id } mysqladmin ping` ).includes( 'alive' ) );
};

process.stdout.write( 'Checking Docker.\n' );

if ( ! quiet( 'docker info --format "{{.ServerVersion}}"' ) ) {
	process.stderr.write( '\nDocker is not running. Start Docker Desktop and try again.\n' );
	process.exit( 1 );
}

const stale = wpEnvContainers();

if ( stale.length ) {
	process.stdout.write( `Removing ${ stale.length } container(s) left from an earlier run.\n` );
	quiet( `docker rm -f ${ stale.join( ' ' ) }` );
}

let started = false;

for ( let attemptNumber = 1; attemptNumber <= START_ATTEMPTS; attemptNumber++ ) {
	if ( attempt( 'npx wp-env start' ) ) {
		started = true;
		break;
	}

	if ( attemptNumber === START_ATTEMPTS ) {
		break;
	}

	process.stdout.write(
		`\nStart attempt ${ attemptNumber } failed. Waiting for MySQL to accept connections`
	);

	const ready = waitFor( mysqlIsReady, MYSQL_TIMEOUT_MS );

	process.stdout.write( ready ? '\nMySQL is up. Starting again.\n' : '\nMySQL never answered.\n' );

	if ( ! ready ) {
		break;
	}
}

if ( ! started ) {
	process.stderr.write( '\nCould not start the environment. The output above has the reason.\n' );
	process.exit( 1 );
}

run( 'npm run env:install-tests' );
run( 'npm run test:php' );

process.stdout.write( '\nEnvironment rebuilt from nothing and the suite passed.\n' );
