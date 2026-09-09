<?php
/**
 * Plugin Name:       DiceBar — Mobile Bottom Bar, Click to Call & Chat
 * Description:       A sticky bottom bar for phones with click to call, chat, directions and social buttons. Works with any theme.
 * Version:           1.6.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Plugin URI:        https://dicecodes.com/dicebar-wordpress-plugin/
 * Author:            Dice Codes
 * Author URI:        https://dicecodes.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dicebar
 * Domain Path:       /languages
 *
 * @package DiceBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DICEBAR_VERSION', '1.6.0' );
define( 'DICEBAR_MIN_PHP', '7.4' );
define( 'DICEBAR_FILE', __FILE__ );
define( 'DICEBAR_PATH', plugin_dir_path( __FILE__ ) );
define( 'DICEBAR_URL', plugin_dir_url( __FILE__ ) );
define( 'DICEBAR_SCHEMA_VERSION', 1 );

/**
 * Whether the running PHP version meets the plugin's floor.
 *
 * Takes the version as an argument so the guard itself is testable.
 *
 * @param string $version Version to check. Defaults to the running version.
 * @return bool
 */
function dicebar_php_is_supported( $version = PHP_VERSION ) {
	return version_compare( $version, DICEBAR_MIN_PHP, '>=' );
}

/**
 * Tell the administrator why the plugin did not load.
 *
 * @return void
 */
function dicebar_php_notice() {
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: 1: required PHP version, 2: running PHP version. */
				__( 'DiceBar needs PHP %1$s or newer. This site runs PHP %2$s.', 'dicebar' ),
				DICEBAR_MIN_PHP,
				PHP_VERSION
			)
		)
	);
}

if ( ! dicebar_php_is_supported() ) {
	add_action( 'admin_notices', 'dicebar_php_notice' );
	return;
}

require_once DICEBAR_PATH . 'includes/class-dicebar-settings.php';
require_once DICEBAR_PATH . 'includes/class-dicebar-sanitize.php';
require_once DICEBAR_PATH . 'includes/class-dicebar-item-types.php';
require_once DICEBAR_PATH . 'includes/class-dicebar-icons.php';
require_once DICEBAR_PATH . 'includes/class-dicebar-styles.php';
require_once DICEBAR_PATH . 'includes/class-dicebar-render.php';
require_once DICEBAR_PATH . 'includes/class-dicebar-assets.php';
require_once DICEBAR_PATH . 'includes/class-dicebar-presets.php';
require_once DICEBAR_PATH . 'includes/class-dicebar-shortcode.php';

if ( is_admin() ) {
	require_once DICEBAR_PATH . 'admin/class-dicebar-admin.php';
	DiceBar_Admin::init();
}

add_action( 'init', array( 'DiceBar_Settings', 'register' ) );
add_action( 'init', array( 'DiceBar_Shortcode', 'register' ) );
add_action( 'wp_enqueue_scripts', array( 'DiceBar_Assets', 'enqueue' ) );
add_filter( 'script_loader_tag', array( 'DiceBar_Assets', 'protect_script' ), 10, 2 );

// Late, so the bar sits after the theme's own footer markup in the source.
add_action( 'wp_footer', array( 'DiceBar_Render', 'footer' ), 100 );
