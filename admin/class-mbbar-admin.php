<?php
/**
 * The settings screen.
 *
 * Server rendered rather than a JavaScript application: it is the whole
 * feature surface in one page, it works with JavaScript disabled, and it adds
 * no build step to the admin.
 *
 * @package MobileBottomBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the settings screen and handles its save.
 */
class MBBar_Admin {

	/**
	 * The capability required to change anything here.
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Menu slug.
	 */
	const SLUG = 'mobile-bottom-bar';

	/**
	 * Hook everything up.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_post_mbbar_save', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( MBBAR_FILE ), array( __CLASS__, 'action_links' ) );
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
			__( 'Mobile Bottom Bar', 'mobile-bottom-bar' ),
			__( 'Mobile Bottom Bar', 'mobile-bottom-bar' ),
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
				'label' => __( 'Items', 'mobile-bottom-bar' ),
				'blurb' => __( 'What sits in the bar. Four is the most that fits.', 'mobile-bottom-bar' ),
			),
			'design'    => array(
				'label' => __( 'Design', 'mobile-bottom-bar' ),
				'blurb' => __( 'The look of the bar, and whether items show icons, words, or both.', 'mobile-bottom-bar' ),
			),
			'placement' => array(
				'label' => __( 'Where', 'mobile-bottom-bar' ),
				'blurb' => __( 'Which screens, which pages, and which visitors see it.', 'mobile-bottom-bar' ),
			),
			'behaviour' => array(
				'label' => __( 'Behaviour', 'mobile-bottom-bar' ),
				'blurb' => __( 'Where it sits, when it gets out of the way, and what it sits above.', 'mobile-bottom-bar' ),
			),
			'place'     => array(
				'label' => __( 'Shortcode', 'mobile-bottom-bar' ),
				'blurb' => __( 'Showing the same row of buttons inside a page.', 'mobile-bottom-bar' ),
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
			esc_html__( 'Settings', 'mobile-bottom-bar' )
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
		wp_enqueue_style( 'mbbar', MBBAR_URL . 'assets/css/mbbar.css', array(), MBBAR_VERSION );
		wp_enqueue_style( 'mbbar-admin', MBBAR_URL . 'admin/assets/admin.css', array( 'mbbar' ), MBBAR_VERSION );
		wp_add_inline_style( 'mbbar', MBBar_Styles::css() );
		wp_enqueue_script( 'mbbar-admin', MBBAR_URL . 'admin/assets/admin.js', array(), MBBAR_VERSION, true );

		wp_localize_script(
			'mbbar-admin',
			'mbbarAdmin',
			array(
				'maxItems'    => 4,
				'types'       => self::types_for_script(),
				'icons'       => MBBar_Icons::names(),
				'brands'      => MBBar_Icons::brand_names(),
				'brandColors' => MBBar_Icons::brand_colors(),
				'presets'     => MBBar_Presets::for_script(),
				'strings'     => array(
					'full'         => __( 'Four items is the most that fits. At 320 pixels wide a fifth one clips its label.', 'mobile-bottom-bar' ),
					'confirm'      => __( 'Remove this item?', 'mobile-bottom-bar' ),
					'untitled'     => __( 'New item', 'mobile-bottom-bar' ),
					'newId'        => __( 'Saved when you save', 'mobile-bottom-bar' ),
					'defaultIcon'  => __( 'Default for this type', 'mobile-bottom-bar' ),
					'label'        => __( 'Label', 'mobile-bottom-bar' ),
					'type'         => __( 'Type', 'mobile-bottom-bar' ),
					'icon'         => __( 'Icon', 'mobile-bottom-bar' ),
					'primary'      => __( 'Make this the standout button', 'mobile-bottom-bar' ),
					'devices'      => __( 'Show on', 'mobile-bottom-bar' ),
					'users'        => __( 'Show to', 'mobile-bottom-bar' ),
					'remove'       => __( 'Remove', 'mobile-bottom-bar' ),
					'replace'      => __( 'This replaces the items you have now. Continue?', 'mobile-bottom-bar' ),
					'applied'      => __( 'Starter kit applied. Fill in the numbers and links, then save.', 'mobile-bottom-bar' ),
					'buttons'      => __( 'buttons', 'mobile-bottom-bar' ),
					'more'         => __( 'Advanced options', 'mobile-bottom-bar' ),
					'less'         => __( 'Hide advanced options', 'mobile-bottom-bar' ),
					'previewEmpty' => __( 'Add an item to see it here.', 'mobile-bottom-bar' ),
					'previewTight' => __( 'That word is long for this many buttons. On a narrow phone it will be cut short. Try a shorter word, one item fewer, or icons only.', 'mobile-bottom-bar' ),
				),
				'choices'     => array(
					'devices' => array(
						'inherit'      => __( 'Whatever the bar does', 'mobile-bottom-bar' ),
						'phone'        => __( 'Phones only', 'mobile-bottom-bar' ),
						'phone_tablet' => __( 'Phones and tablets', 'mobile-bottom-bar' ),
						'all'          => __( 'Every screen', 'mobile-bottom-bar' ),
					),
					'users'   => array(
						'inherit' => __( 'Whatever the bar does', 'mobile-bottom-bar' ),
						'all'     => __( 'Everyone', 'mobile-bottom-bar' ),
						'in'      => __( 'Signed in visitors', 'mobile-bottom-bar' ),
						'out'     => __( 'Signed out visitors', 'mobile-bottom-bar' ),
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

		foreach ( MBBar_Item_Types::all() as $slug => $type ) {
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
		if ( ! isset( $_POST['mbbar_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mbbar_nonce'] ) ), 'mbbar_save' ) ) {
			wp_die( esc_html__( 'That form has expired. Please go back and try again.', 'mobile-bottom-bar' ) );
		}

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to change these settings.', 'mobile-bottom-bar' ) );
		}

		// The whole payload goes through the sanitiser, which rebuilds it from
		// the defaults and keeps nothing it does not recognise.
		$raw = isset( $_POST['mbbar'] ) && is_array( $_POST['mbbar'] )
			? wp_unslash( $_POST['mbbar'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();

		update_option( MBBar_Settings::OPTION, MBBar_Sanitize::settings( $raw ) );
		MBBar_Settings::flush();

		$tab = isset( $_POST['mbbar_tab'] ) ? sanitize_key( wp_unslash( $_POST['mbbar_tab'] ) ) : 'items';

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
			wp_die( esc_html__( 'You do not have permission to view this page.', 'mobile-bottom-bar' ) );
		}

		$settings = MBBar_Settings::get();
		$types    = MBBar_Item_Types::all();
		$icons    = MBBar_Icons::names();
		$tabs     = self::tabs();
		$presets  = MBBar_Presets::all();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Choosing a tab reads nothing and changes nothing.
		$current = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'items';

		if ( ! array_key_exists( $current, $tabs ) ) {
			$current = 'items';
		}

		require MBBAR_PATH . 'admin/views/settings-page.php';
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
