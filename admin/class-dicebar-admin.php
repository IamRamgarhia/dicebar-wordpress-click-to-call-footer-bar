<?php
/**
 * The settings screen.
 *
 * Server rendered rather than a JavaScript application: it is the whole
 * feature surface in one page, it works with JavaScript disabled, and it adds
 * no build step to the admin.
 *
 * @package DiceBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the settings screen and handles its save.
 */
class DiceBar_Admin {

	/**
	 * The capability required to change anything here.
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Menu slug.
	 */
	const SLUG = 'dicebar';

	/**
	 * Hook everything up.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_post_dicebar_save', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( DICEBAR_FILE ), array( __CLASS__, 'action_links' ) );
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
			__( 'DiceBar', 'dicebar' ),
			__( 'DiceBar', 'dicebar' ),
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
				'label' => __( 'Items', 'dicebar' ),
				'blurb' => __( 'What sits in the bar. Four is the most that fits.', 'dicebar' ),
			),
			'design'    => array(
				'label' => __( 'Design', 'dicebar' ),
				'blurb' => __( 'The look of the bar, and whether items show icons, words, or both.', 'dicebar' ),
			),
			'placement' => array(
				'label' => __( 'Where', 'dicebar' ),
				'blurb' => __( 'Which screens, which pages, and which visitors see it.', 'dicebar' ),
			),
			'behaviour' => array(
				'label' => __( 'Behaviour', 'dicebar' ),
				'blurb' => __( 'Where it sits, when it gets out of the way, and what it sits above.', 'dicebar' ),
			),
			'place'     => array(
				'label' => __( 'Shortcode', 'dicebar' ),
				'blurb' => __( 'Showing the same row of buttons inside a page.', 'dicebar' ),
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
			esc_html__( 'Settings', 'dicebar' )
		);

		$docs = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( DICEBAR_DOCS ),
			esc_html__( 'Docs', 'dicebar' )
		);

		array_unshift( $links, $settings, $docs );

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
		wp_enqueue_style( 'dicebar', DICEBAR_URL . 'assets/css/dicebar.css', array(), DICEBAR_VERSION );
		wp_enqueue_style( 'dicebar-admin', DICEBAR_URL . 'admin/assets/admin.css', array( 'dicebar' ), DICEBAR_VERSION );
		wp_add_inline_style( 'dicebar', DiceBar_Styles::css() );
		wp_enqueue_script( 'dicebar-admin', DICEBAR_URL . 'admin/assets/admin.js', array(), DICEBAR_VERSION, true );

		wp_localize_script(
			'dicebar-admin',
			'dicebarAdmin',
			array(
				'maxItems'    => 4,
				'types'       => self::types_for_script(),
				'icons'       => DiceBar_Icons::names(),
				'brands'      => DiceBar_Icons::brand_names(),
				'brandColors' => DiceBar_Icons::brand_colors(),
				'presets'     => DiceBar_Presets::for_script(),
				'strings'     => array(
					'full'         => __( 'Four items is the most that fits. At 320 pixels wide a fifth one clips its label.', 'dicebar' ),
					'confirm'      => __( 'Remove this item?', 'dicebar' ),
					'untitled'     => __( 'New item', 'dicebar' ),
					'newId'        => __( 'Saved when you save', 'dicebar' ),
					'defaultIcon'  => __( 'Default for this type', 'dicebar' ),
					'label'        => __( 'Label', 'dicebar' ),
					'type'         => __( 'Type', 'dicebar' ),
					'icon'         => __( 'Icon', 'dicebar' ),
					'primary'      => __( 'Make this the standout button', 'dicebar' ),
					'devices'      => __( 'Show on', 'dicebar' ),
					'users'        => __( 'Show to', 'dicebar' ),
					'remove'       => __( 'Remove', 'dicebar' ),
					'replace'      => __( 'This replaces the items you have now. Continue?', 'dicebar' ),
					'applied'      => __( 'Starter kit applied. Fill in the numbers and links, then save.', 'dicebar' ),
					'buttons'      => __( 'buttons', 'dicebar' ),
					'more'         => __( 'Advanced options', 'dicebar' ),
					'less'         => __( 'Hide advanced options', 'dicebar' ),
					'previewEmpty' => __( 'Add an item to see it here.', 'dicebar' ),
					'previewTight' => __( 'That word is long for this many buttons. On a narrow phone it will be cut short. Try a shorter word, one item fewer, or icons only.', 'dicebar' ),
				),
				'choices'     => array(
					'devices' => array(
						'inherit'      => __( 'Whatever the bar does', 'dicebar' ),
						'phone'        => __( 'Phones only', 'dicebar' ),
						'phone_tablet' => __( 'Phones and tablets', 'dicebar' ),
						'all'          => __( 'Every screen', 'dicebar' ),
					),
					'users'   => array(
						'inherit' => __( 'Whatever the bar does', 'dicebar' ),
						'all'     => __( 'Everyone', 'dicebar' ),
						'in'      => __( 'Signed in visitors', 'dicebar' ),
						'out'     => __( 'Signed out visitors', 'dicebar' ),
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

		foreach ( DiceBar_Item_Types::all() as $slug => $type ) {
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
		if ( ! isset( $_POST['dicebar_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dicebar_nonce'] ) ), 'dicebar_save' ) ) {
			wp_die( esc_html__( 'That form has expired. Please go back and try again.', 'dicebar' ) );
		}

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to change these settings.', 'dicebar' ) );
		}

		// The whole payload goes through the sanitiser, which rebuilds it from
		// the defaults and keeps nothing it does not recognise.
		$raw = isset( $_POST['dicebar'] ) && is_array( $_POST['dicebar'] )
			? wp_unslash( $_POST['dicebar'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();

		update_option( DiceBar_Settings::OPTION, DiceBar_Sanitize::settings( $raw ) );
		DiceBar_Settings::flush();

		$tab = isset( $_POST['dicebar_tab'] ) ? sanitize_key( wp_unslash( $_POST['dicebar_tab'] ) ) : 'items';

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
			wp_die( esc_html__( 'You do not have permission to view this page.', 'dicebar' ) );
		}

		$settings = DiceBar_Settings::get();
		$types    = DiceBar_Item_Types::all();
		$icons    = DiceBar_Icons::names();
		$tabs     = self::tabs();
		$presets  = DiceBar_Presets::all();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Choosing a tab reads nothing and changes nothing.
		$current = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'items';

		if ( ! array_key_exists( $current, $tabs ) ) {
			$current = 'items';
		}

		require DICEBAR_PATH . 'admin/views/settings-page.php';
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
