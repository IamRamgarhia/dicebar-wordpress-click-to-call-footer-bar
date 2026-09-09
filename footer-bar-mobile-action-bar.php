<?php
/**
 * Plugin Name:       Footer Bar — Mobile Action Bar
 * Description:       A bottom bar on phones and tablets holding whatever you put in it: call, message, links, an announcement.
 * Version:           1.2.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Prince
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       footer-bar-mobile-action-bar
 * Domain Path:       /languages
 *
 * @package FooterBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FBAR_VERSION', '1.2.0' );
define( 'FBAR_MIN_PHP', '7.4' );
define( 'FBAR_FILE', __FILE__ );
define( 'FBAR_PATH', plugin_dir_path( __FILE__ ) );
define( 'FBAR_URL', plugin_dir_url( __FILE__ ) );
define( 'FBAR_SCHEMA_VERSION', 1 );

/**
 * Whether the running PHP version meets the plugin's floor.
 *
 * Takes the version as an argument so the guard itself is testable.
 *
 * @param string $version Version to check. Defaults to the running version.
 * @return bool
 */
function fbar_php_is_supported( $version = PHP_VERSION ) {
	return version_compare( $version, FBAR_MIN_PHP, '>=' );
}

/**
 * Tell the administrator why the plugin did not load.
 *
 * @return void
 */
function fbar_php_notice() {
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: 1: required PHP version, 2: running PHP version. */
				__( 'Footer Bar needs PHP %1$s or newer. This site runs PHP %2$s.', 'footer-bar-mobile-action-bar' ),
				FBAR_MIN_PHP,
				PHP_VERSION
			)
		)
	);
}

if ( ! fbar_php_is_supported() ) {
	add_action( 'admin_notices', 'fbar_php_notice' );
	return;
}

require_once FBAR_PATH . 'includes/class-fbar-settings.php';
require_once FBAR_PATH . 'includes/class-fbar-sanitize.php';
require_once FBAR_PATH . 'includes/class-fbar-item-types.php';
require_once FBAR_PATH . 'includes/class-fbar-icons.php';
require_once FBAR_PATH . 'includes/class-fbar-styles.php';
require_once FBAR_PATH . 'includes/class-fbar-render.php';
require_once FBAR_PATH . 'includes/class-fbar-assets.php';
require_once FBAR_PATH . 'includes/class-fbar-presets.php';
require_once FBAR_PATH . 'includes/class-fbar-shortcode.php';

if ( is_admin() ) {
	require_once FBAR_PATH . 'admin/class-fbar-admin.php';
	FBar_Admin::init();
}

add_action( 'init', array( 'FBar_Settings', 'register' ) );
add_action( 'init', array( 'FBar_Shortcode', 'register' ) );
add_action( 'wp_enqueue_scripts', array( 'FBar_Assets', 'enqueue' ) );
add_filter( 'script_loader_tag', array( 'FBar_Assets', 'protect_script' ), 10, 2 );

// Late, so the bar sits after the theme's own footer markup in the source.
add_action( 'wp_footer', array( 'FBar_Render', 'footer' ), 100 );
