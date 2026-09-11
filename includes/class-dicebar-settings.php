<?php
/**
 * Settings defaults, accessor and registration.
 *
 * @package DiceBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Holds the configuration shape, its defaults, and the accessor.
 */
class DiceBar_Settings {

	/**
	 * Option holding the whole configuration. Autoloaded.
	 */
	const OPTION = 'dicebar_settings';

	/**
	 * Option holding the schema version. Not autoloaded.
	 */
	const VERSION_OPTION = 'dicebar_version';

	/**
	 * How the page rule is applied, in the order the settings screen shows them.
	 *
	 * Declared here rather than written inline in the view, because a literal
	 * array key of "exclude" trips a static-analysis sniff that assumes it is a
	 * query argument.
	 */
	const CONTENT_MODES = array( 'all', 'include', 'exclude' );

	/**
	 * Ready-made looks.
	 *
	 * A preset writes several values at once. "Glass" is the frosted, heavily
	 * blurred panel people mean when they say an Apple-like bar: high blur, a
	 * lifted white edge, and a generous corner radius.
	 */
	const PRESETS = array( 'glass', 'solid', 'minimal', 'bold', 'custom' );

	/**
	 * What each item shows.
	 */
	const LABEL_MODES = array( 'icon_label', 'icon', 'label' );

	/**
	 * Cached configuration for this request.
	 *
	 * @var array|null
	 */
	private static $cache = null;

	/**
	 * The default configuration.
	 *
	 * Every number here is a measured value from the design spec. They are
	 * defaults, not constants: each one is settable.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'enabled'                 => true,

			'display'                 => array(
				'devices'    => 'phone_tablet',
				'breakpoint' => 1023,
				'phone_max'  => 767,
				'content'    => array(
					'mode' => 'all',
					'ids'  => array(),
				),
				'users'      => 'all',
			),

			'behaviour'               => array(
				'position'      => 'bottom',
				'appear'        => 'always',
				'entrance'      => 'slide',
				'hide_selector' => '',
				'clearance'     => 0,
				'z_index'       => 9990,
			),

			'style'                   => array(
				'preset'      => 'glass',
				'brand_icons' => true,
				'glass'       => 22,
				'opacity'     => 78,
				'layout'      => 'island',
				'max_width'   => 640,
				'radius'      => 18,
				'shadow'      => 'soft',
				'blur'        => true,
				'divider'     => 'hairline',
				'gap'         => 11,
				'item'        => array(
					'shape'      => 'plain',
					'radius'     => 13,
					'min_height' => 52,
					'icon_size'  => 18,
					'icon_gap'   => 3,
				),
				'label'       => array(
					'mode' => 'icon_label',
					'size' => 10,
					'case' => 'upper',
				),
				'scheme'      => 'light',
				'light'       => array(
					'bar_bg'     => '#ffffff',
					'text'       => '#1c1c1e',
					'accent'     => '#0a84ff',
					'icon'       => '#1c1c1e',
					'hover_bg'   => '#0a84ff',
					'hover_text' => '#ffffff',
					'bold_bg'    => '#0a84ff',
					'divider'    => 'rgba(0,0,0,0.12)',
				),
				'dark'        => array(
					'bar_bg'     => '#1c1c1e',
					'text'       => '#f2f2f7',
					'accent'     => '#0a84ff',
					'icon'       => '#f2f2f7',
					'hover_bg'   => '#0a84ff',
					'hover_text' => '#ffffff',
					'bold_bg'    => '#0a84ff',
					'divider'    => 'rgba(255,255,255,0.16)',
				),
			),

			'items'                   => array(),

			'keep_settings_on_delete' => false,
		);
	}

	/**
	 * The default shape of a single item, without an id.
	 *
	 * @return array
	 */
	public static function item_defaults() {
		return array(
			'type'    => 'link',
			'label'   => '',
			'value'   => '',
			'extra'   => array(),
			'icon'    => '',
			'primary' => false,
			'show'    => array(
				'devices' => 'inherit',
				'users'   => 'inherit',
			),
		);
	}

	/**
	 * The stored configuration, merged over the defaults.
	 *
	 * Cached per request because the bar and its assets both read it.
	 *
	 * @return array
	 */
	public static function get() {
		if ( null !== self::$cache ) {
			return self::$cache;
		}

		$stored = get_option( self::OPTION, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		self::$cache = self::merge( self::defaults(), $stored );

		return self::$cache;
	}

	/**
	 * Forget the cached configuration.
	 *
	 * @return void
	 */
	public static function flush() {
		self::$cache = null;
	}

	/**
	 * Merge stored values over defaults, recursing into associative arrays only.
	 *
	 * List arrays such as items and id lists replace wholesale. Merging them
	 * would make it impossible to remove the last entry.
	 *
	 * @param array $defaults Default values.
	 * @param array $stored   Stored values.
	 * @return array
	 */
	private static function merge( array $defaults, array $stored ) {
		foreach ( $stored as $key => $value ) {
			if ( ! array_key_exists( $key, $defaults ) ) {
				continue;
			}

			if ( is_array( $defaults[ $key ] ) && is_array( $value ) && self::is_assoc( $defaults[ $key ] ) ) {
				$defaults[ $key ] = self::merge( $defaults[ $key ], $value );
				continue;
			}

			$defaults[ $key ] = $value;
		}

		return $defaults;
	}

	/**
	 * Whether an array is associative rather than a plain list.
	 *
	 * An empty array counts as a list, which is what makes the items array and
	 * empty id lists replace rather than merge.
	 *
	 * @param array $value Array to inspect.
	 * @return bool
	 */
	private static function is_assoc( array $value ) {
		if ( array() === $value ) {
			return false;
		}

		return array_keys( $value ) !== range( 0, count( $value ) - 1 );
	}

	/**
	 * Register the option so it is available to the settings screen.
	 *
	 * @return void
	 */
	public static function register() {
		register_setting(
			'dicebar',
			self::OPTION,
			array(
				'type'              => 'object',
				'default'           => self::defaults(),
				'sanitize_callback' => array( 'DiceBar_Sanitize', 'settings' ),
				'show_in_rest'      => false,
			)
		);
	}
}
