<?php
/**
 * PHPUnit bootstrap. Loads the WordPress test suite and this plugin.
 *
 * @package FooterBar
 */

$fbar_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $fbar_tests_dir ) {
	$fbar_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $fbar_tests_dir . '/includes/functions.php' ) ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI diagnostic output, printed before WordPress loads; there is no browser to escape for.
	echo "Could not find the WordPress test suite at {$fbar_tests_dir}.\n";
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI diagnostic output, printed before WordPress loads; there is no browser to escape for.
	echo "Run: npm run env:start && npm run env:install-tests\n";
	exit( 1 );
}

require_once $fbar_tests_dir . '/includes/functions.php';

/**
 * Load the plugin before WordPress finishes booting.
 */
function fbar_manually_load_plugin() {
	require dirname( __DIR__, 2 ) . '/footer-bar-mobile-action-bar.php';
}

tests_add_filter( 'muplugins_loaded', 'fbar_manually_load_plugin' );

require $fbar_tests_dir . '/includes/bootstrap.php';
