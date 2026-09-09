<?php
/**
 * Plugin Name:       Mobile Bottom Bar — Click to Call & Chat Buttons
 * Description:       A sticky bottom bar for phones with click to call, chat, directions and social buttons. Works with any theme.
 * Version:           1.6.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Plugin URI:        https://dicecodes.com/mobile-bottom-bar-wordpress-plugin/
 * Author:            Dice Codes
 * Author URI:        https://dicecodes.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mobile-bottom-bar
 * Domain Path:       /languages
 *
 * @package MobileBottomBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MBBAR_VERSION', '1.6.0' );
define( 'MBBAR_MIN_PHP', '7.4' );
define( 'MBBAR_FILE', __FILE__ );
define( 'MBBAR_PATH', plugin_dir_path( __FILE__ ) );
define( 'MBBAR_URL', plugin_dir_url( __FILE__ ) );
define( 'MBBAR_SCHEMA_VERSION', 1 );

/**
 * Whether the running PHP version meets the plugin's floor.
 *
 * Takes the version as an argument so the guard itself is testable.
 *
 * @param string $version Version to check. Defaults to the running version.
 * @return bool
 */
function mbbar_php_is_supported( $version = PHP_VERSION ) {
	return version_compare( $version, MBBAR_MIN_PHP, '>=' );
}

/**
 * Tell the administrator why the plugin did not load.
 *
 * @return void
 */
function mbbar_php_notice() {
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: 1: required PHP version, 2: running PHP version. */
				__( 'Mobile Bottom Bar needs PHP %1$s or newer. This site runs PHP %2$s.', 'mobile-bottom-bar' ),
				MBBAR_MIN_PHP,
				PHP_VERSION
			)
		)
	);
}

if ( ! mbbar_php_is_supported() ) {
	add_action( 'admin_notices', 'mbbar_php_notice' );
	return;
}

require_once MBBAR_PATH . 'includes/class-mbbar-settings.php';
require_once MBBAR_PATH . 'includes/class-mbbar-sanitize.php';
require_once MBBAR_PATH . 'includes/class-mbbar-item-types.php';
require_once MBBAR_PATH . 'includes/class-mbbar-icons.php';
require_once MBBAR_PATH . 'includes/class-mbbar-styles.php';
require_once MBBAR_PATH . 'includes/class-mbbar-render.php';
require_once MBBAR_PATH . 'includes/class-mbbar-assets.php';
require_once MBBAR_PATH . 'includes/class-mbbar-presets.php';
require_once MBBAR_PATH . 'includes/class-mbbar-shortcode.php';

if ( is_admin() ) {
	require_once MBBAR_PATH . 'admin/class-mbbar-admin.php';
	MBBar_Admin::init();
}

add_action( 'init', array( 'MBBar_Settings', 'register' ) );
add_action( 'init', array( 'MBBar_Shortcode', 'register' ) );
add_action( 'wp_enqueue_scripts', array( 'MBBar_Assets', 'enqueue' ) );
add_filter( 'script_loader_tag', array( 'MBBar_Assets', 'protect_script' ), 10, 2 );

// Late, so the bar sits after the theme's own footer markup in the source.
add_action( 'wp_footer', array( 'MBBar_Render', 'footer' ), 100 );
