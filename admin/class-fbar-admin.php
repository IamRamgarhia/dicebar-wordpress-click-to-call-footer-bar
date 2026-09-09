<?php
/**
 * The settings screen.
 *
 * Server rendered rather than a JavaScript application: it is the whole
 * feature surface in one page, it works with JavaScript disabled, and it adds
 * no build step to the admin.
 *
 * @package FooterBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the settings screen and handles its save.
 */
class FBar_Admin {

	/**
	 * The capability required to change anything here.
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Menu slug.
	 */
	const SLUG = 'footerbar';

	/**
	 * Hook everything up.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_post_fbar_save', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( FBAR_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Add a top-level menu.
	 *
	 * Top level rather than buried under Settings: this is a screen people
	 * come back to and edit, not a one-time configuration they set and forget.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_menu_page(
			__( 'Footer Bar', 'footer-bar-mobile-action-bar' ),
			__( 'Footer Bar', 'footer-bar-mobile-action-bar' ),
			self::CAPABILITY,
			self::SLUG,
			array( __CLASS__, 'render_page' ),
			'dashicons-smartphone',
			58
		);
	}

	/**
	 * The screen's tabs, in order.
	 *
	 * @return array Tab slugs mapped to their labels and descriptions.
	 */
	public static function tabs() {
		return array(
			'items'     => array(
				'label' => __( 'Items', 'footer-bar-mobile-action-bar' ),
				'blurb' => __( 'What sits in the bar. Four is the most that fits.', 'footer-bar-mobile-action-bar' ),
			),
			'design'    => array(
				'label' => __( 'Design', 'footer-bar-mobile-action-bar' ),
				'blurb' => __( 'The look of the bar, and whether items show icons, words, or both.', 'footer-bar-mobile-action-bar' ),
			),
			'placement' => array(
				'label' => __( 'Where', 'footer-bar-mobile-action-bar' ),
				'blurb' => __( 'Which screens, which pages, and which visitors see it.', 'footer-bar-mobile-action-bar' ),
			),
			'behaviour' => array(
				'label' => __( 'Behaviour', 'footer-bar-mobile-action-bar' ),
				'blurb' => __( 'Where it sits, when it gets out of the way, and what it sits above.', 'footer-bar-mobile-action-bar' ),
			),
			'place'     => array(
				'label' => __( 'Shortcode', 'footer-bar-mobile-action-bar' ),
				'blurb' => __( 'Showing the same row of buttons inside a page.', 'footer-bar-mobile-action-bar' ),
			),
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
			esc_url( admin_url( 'admin.php?page=' . self::SLUG ) ),
			esc_html__( 'Settings', 'footer-bar-mobile-action-bar' )
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
		if ( 'toplevel_page_' . self::SLUG !== $hook ) {
			return;
		}

		// The preview is the real bar, styled by the real stylesheet, so it
		// cannot drift from what visitors see.
		wp_enqueue_style( 'fbar', FBAR_URL . 'assets/css/fbar.css', array(), FBAR_VERSION );
		wp_enqueue_style( 'fbar-admin', FBAR_URL . 'admin/assets/admin.css', array( 'fbar' ), FBAR_VERSION );
		wp_enqueue_script( 'fbar-admin', FBAR_URL . 'admin/assets/admin.js', array(), FBAR_VERSION, true );

		wp_localize_script(
			'fbar-admin',
			'fbarAdmin',
			array(
				'maxItems' => 4,
				'types'    => self::types_for_script(),
				'icons'    => FBar_Icons::names(),
				'brands'   => FBar_Icons::brand_names(),
				'presets'  => FBar_Presets::for_script(),
				'strings'  => array(
					'full'         => __( 'Four items is the most that fits. At 320 pixels wide a fifth one clips its label.', 'footer-bar-mobile-action-bar' ),
					'confirm'      => __( 'Remove this item?', 'footer-bar-mobile-action-bar' ),
					'untitled'     => __( 'New item', 'footer-bar-mobile-action-bar' ),
					'newId'        => __( 'Saved when you save', 'footer-bar-mobile-action-bar' ),
					'defaultIcon'  => __( 'Default for this type', 'footer-bar-mobile-action-bar' ),
					'label'        => __( 'Label', 'footer-bar-mobile-action-bar' ),
					'type'         => __( 'Type', 'footer-bar-mobile-action-bar' ),
					'icon'         => __( 'Icon', 'footer-bar-mobile-action-bar' ),
					'primary'      => __( 'Make this the standout button', 'footer-bar-mobile-action-bar' ),
					'devices'      => __( 'Show on', 'footer-bar-mobile-action-bar' ),
					'users'        => __( 'Show to', 'footer-bar-mobile-action-bar' ),
					'remove'       => __( 'Remove', 'footer-bar-mobile-action-bar' ),
					'replace'      => __( 'This replaces the items you have now. Continue?', 'footer-bar-mobile-action-bar' ),
					'applied'      => __( 'Starter kit applied. Fill in the numbers and links, then save.', 'footer-bar-mobile-action-bar' ),
					'buttons'      => __( 'buttons', 'footer-bar-mobile-action-bar' ),
					'more'         => __( 'Advanced options', 'footer-bar-mobile-action-bar' ),
					'less'         => __( 'Hide advanced options', 'footer-bar-mobile-action-bar' ),
					'previewEmpty' => __( 'Add an item to see it here.', 'footer-bar-mobile-action-bar' ),
					'previewTight' => __( 'That word is long for this many buttons. On a narrow phone it will be cut short. Try a shorter word, one item fewer, or icons only.', 'footer-bar-mobile-action-bar' ),
				),
				'choices'  => array(
					'devices' => array(
						'inherit'      => __( 'Whatever the bar does', 'footer-bar-mobile-action-bar' ),
						'phone'        => __( 'Phones only', 'footer-bar-mobile-action-bar' ),
						'phone_tablet' => __( 'Phones and tablets', 'footer-bar-mobile-action-bar' ),
						'all'          => __( 'Every screen', 'footer-bar-mobile-action-bar' ),
					),
					'users'   => array(
						'inherit' => __( 'Whatever the bar does', 'footer-bar-mobile-action-bar' ),
						'all'     => __( 'Everyone', 'footer-bar-mobile-action-bar' ),
						'in'      => __( 'Signed in visitors', 'footer-bar-mobile-action-bar' ),
						'out'     => __( 'Signed out visitors', 'footer-bar-mobile-action-bar' ),
					),
				),
			)
		);
	}

	/**
	 * The item types in the shape the admin script needs.
	 *
	 * The script rebuilds an item's fields when its type changes, so it needs
	 * to know what each type asks for without another request.
	 *
	 * @return array
	 */
	private static function types_for_script() {
		$out = array();

		foreach ( FBar_Item_Types::all() as $slug => $type ) {
			$extra = array();

			foreach ( $type['extra'] as $key => $field ) {
				$extra[] = array(
					'key'   => $key,
					'kind'  => $field['kind'],
					'label' => $field['label'],
				);
			}

			$out[] = array(
				'slug'       => $slug,
				'label'      => $type['label'],
				'icon'       => $type['icon'],
				'valueLabel' => $type['value']['label'],
				'valueKind'  => $type['value']['kind'],
				'extra'      => $extra,
			);
		}

		return $out;
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
		if ( ! isset( $_POST['fbar_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['fbar_nonce'] ) ), 'fbar_save' ) ) {
			wp_die( esc_html__( 'That form has expired. Please go back and try again.', 'footer-bar-mobile-action-bar' ) );
		}

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to change these settings.', 'footer-bar-mobile-action-bar' ) );
		}

		// The whole payload goes through the sanitiser, which rebuilds it from
		// the defaults and keeps nothing it does not recognise.
		$raw = isset( $_POST['fbar'] ) && is_array( $_POST['fbar'] )
			? wp_unslash( $_POST['fbar'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();

		update_option( FBar_Settings::OPTION, FBar_Sanitize::settings( $raw ) );
		FBar_Settings::flush();

		$tab = isset( $_POST['fbar_tab'] ) ? sanitize_key( wp_unslash( $_POST['fbar_tab'] ) ) : 'items';

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => self::SLUG,
					'tab'     => array_key_exists( $tab, self::tabs() ) ? $tab : 'items',
					'updated' => '1',
				),
				admin_url( 'admin.php' )
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
			wp_die( esc_html__( 'You do not have permission to view this page.', 'footer-bar-mobile-action-bar' ) );
		}

		$settings = FBar_Settings::get();
		$types    = FBar_Item_Types::all();
		$icons    = FBar_Icons::names();
		$tabs     = self::tabs();
		$presets  = FBar_Presets::all();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Choosing a tab reads nothing and changes nothing.
		$current = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'items';

		if ( ! array_key_exists( $current, $tabs ) ) {
			$current = 'items';
		}

		require FBAR_PATH . 'admin/views/settings-page.php';
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
