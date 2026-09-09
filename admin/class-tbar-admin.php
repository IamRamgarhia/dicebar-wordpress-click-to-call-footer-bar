<?php
/**
 * The settings screen.
 *
 * Server rendered rather than a JavaScript application: it is the whole
 * feature surface in one page, it works with JavaScript disabled, and it adds
 * no build step to the admin.
 *
 * @package TapBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the settings screen and handles its save.
 */
class TBar_Admin {

	/**
	 * The capability required to change anything here.
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Menu slug.
	 */
	const SLUG = 'tapbar';

	/**
	 * Hook everything up.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_post_tbar_save', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( TBAR_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Add the settings page under Settings.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_options_page(
			__( 'TapBar', 'tapbar-mobile-action-bar' ),
			__( 'TapBar', 'tapbar-mobile-action-bar' ),
			self::CAPABILITY,
			self::SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * A Settings link on the plugins screen.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$settings = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=' . self::SLUG ) ),
			esc_html__( 'Settings', 'tapbar-mobile-action-bar' )
		);

		array_unshift( $links, $settings );

		return $links;
	}

	/**
	 * Load the screen's own assets, and only on its own screen.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue( $hook ) {
		if ( 'settings_page_' . self::SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style( 'tbar-admin', TBAR_URL . 'admin/assets/admin.css', array(), TBAR_VERSION );
		wp_enqueue_script( 'tbar-admin', TBAR_URL . 'admin/assets/admin.js', array(), TBAR_VERSION, true );

		wp_localize_script(
			'tbar-admin',
			'tbarAdmin',
			array(
				'maxItems'    => 4,
				'maxNotice'   => __( 'Four items is the most that fits. At 320 pixels wide a fifth item clips its label.', 'tapbar-mobile-action-bar' ),
				'confirmText' => __( 'Remove this item?', 'tapbar-mobile-action-bar' ),
			)
		);
	}

	/**
	 * Save handler.
	 *
	 * A nonce is not an authorisation check and a capability check is not a
	 * CSRF check. Both are needed, in that order.
	 *
	 * @return void
	 */
	public static function handle_save() {
		if ( ! isset( $_POST['tbar_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['tbar_nonce'] ) ), 'tbar_save' ) ) {
			wp_die( esc_html__( 'That form has expired. Please go back and try again.', 'tapbar-mobile-action-bar' ) );
		}

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to change these settings.', 'tapbar-mobile-action-bar' ) );
		}

		// The whole payload goes through the sanitiser, which rebuilds it from
		// the defaults and keeps nothing it does not recognise.
		$raw = isset( $_POST['tbar'] ) && is_array( $_POST['tbar'] )
			? wp_unslash( $_POST['tbar'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();

		update_option( TBar_Settings::OPTION, TBar_Sanitize::settings( $raw ) );
		TBar_Settings::flush();

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => self::SLUG,
					'updated' => '1',
				),
				admin_url( 'options-general.php' )
			)
		);

		exit;
	}

	/**
	 * Render the settings screen.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'tapbar-mobile-action-bar' ) );
		}

		$settings = TBar_Settings::get();
		$types    = TBar_Item_Types::all();
		$icons    = TBar_Icons::names();

		require TBAR_PATH . 'admin/views/settings-page.php';
	}

	/**
	 * A select control.
	 *
	 * @param string $name    Field name.
	 * @param array  $choices Value to label map.
	 * @param string $current Current value.
	 * @return void
	 */
	public static function select( $name, array $choices, $current ) {
		printf( '<select name="%s">', esc_attr( $name ) );

		foreach ( $choices as $value => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $value ),
				selected( (string) $value, (string) $current, false ),
				esc_html( $label )
			);
		}

		echo '</select>';
	}
}
