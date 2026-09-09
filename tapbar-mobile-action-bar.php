<?php
/**
 * Plugin Name:       TapBar — Mobile Action Bar
 * Plugin URI:        https://example.com/tapbar
 * Description:       A bottom bar on phones and tablets holding whatever you put in it: call, message, links, an announcement.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Prince
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tapbar-mobile-action-bar
 * Domain Path:       /languages
 *
 * @package TapBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TBAR_VERSION', '1.0.0' );
define( 'TBAR_MIN_PHP', '7.4' );
define( 'TBAR_FILE', __FILE__ );
define( 'TBAR_PATH', plugin_dir_path( __FILE__ ) );
define( 'TBAR_URL', plugin_dir_url( __FILE__ ) );
define( 'TBAR_SCHEMA_VERSION', 1 );

/**
 * Whether the running PHP version meets the plugin's floor.
 *
 * Takes the version as an argument so the guard itself is testable.
 *
 * @param string $version Version to check. Defaults to the running version.
 * @return bool
 */
function tbar_php_is_supported( $version = PHP_VERSION ) {
	return version_compare( $version, TBAR_MIN_PHP, '>=' );
}

/**
 * Tell the administrator why the plugin did not load.
 *
 * @return void
 */
function tbar_php_notice() {
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: 1: required PHP version, 2: running PHP version. */
				__( 'TapBar needs PHP %1$s or newer. This site runs PHP %2$s.', 'tapbar-mobile-action-bar' ),
				TBAR_MIN_PHP,
				PHP_VERSION
			)
		)
	);
}

if ( ! tbar_php_is_supported() ) {
	add_action( 'admin_notices', 'tbar_php_notice' );
	return;
}
