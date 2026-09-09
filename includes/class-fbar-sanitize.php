<?php
/**
 * Sanitisers.
 *
 * Every method takes an untrusted value and returns one of the documented
 * type. None throw and none return null: a bad value becomes the fallback,
 * because a settings save must never store half a configuration.
 *
 * @package FooterBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Primitive and structural sanitisers for the configuration.
 */
class FBar_Sanitize {

	/**
	 * The only URL schemes this plugin will ever put in an href.
	 *
	 * Deliberately narrower than wp_allowed_protocols(). An item URL comes from
	 * an administrator, and administrator accounts get compromised.
	 */
	const ALLOWED_SCHEMES = array( 'http', 'https', 'tel', 'mailto', 'sms' );

	/**
	 * An integer inside a range, or the fallback.
	 *
	 * Out of range falls back rather than clamping, because a clamped value
	 * silently disagrees with what the administrator typed.
	 *
	 * @param mixed $value    Untrusted value.
	 * @param int   $min      Lowest acceptable value.
	 * @param int   $max      Highest acceptable value.
	 * @param int   $fallback Value when the input is unusable.
	 * @return int
	 */
	public static function int_in_range( $value, $min, $max, $fallback ) {
		if ( is_array( $value ) || is_object( $value ) || ! is_numeric( $value ) ) {
			return (int) $fallback;
		}

		$value = (int) $value;

		return ( $value >= $min && $value <= $max ) ? $value : (int) $fallback;
	}

	/**
	 * One of a fixed set of strings, or the fallback.
	 *
	 * @param mixed  $value    Untrusted value.
	 * @param array  $allowed  Permitted values.
	 * @param string $fallback Value when the input is not permitted.
	 * @return string
	 */
	public static function choice( $value, array $allowed, $fallback ) {
		return in_array( $value, $allowed, true ) ? $value : (string) $fallback;
	}

	/**
	 * A boolean, reading the string forms forms and REST send.
	 *
	 * @param mixed $value Untrusted value.
	 * @return bool
	 */
	public static function boolean( $value ) {
		return rest_sanitize_boolean( $value );
	}

	/**
	 * A CSS colour: hex, rgb, rgba, or the transparent keyword.
	 *
	 * Anything else becomes the fallback. These values are written into an
	 * inline style attribute, so a value carrying a semicolon or a url() would
	 * let an administrator inject arbitrary declarations.
	 *
	 * @param mixed  $value    Untrusted value.
	 * @param string $fallback Value when the input is unusable.
	 * @return string
	 */
	public static function color( $value, $fallback ) {
		if ( ! is_string( $value ) ) {
			return $fallback;
		}

		$value = trim( $value );

		if ( '' === $value ) {
			return $fallback;
		}

		if ( 'transparent' === strtolower( $value ) ) {
			return 'transparent';
		}

		$hex = sanitize_hex_color( $value );

		if ( $hex ) {
			return $hex;
		}

		$rgb = '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(?:,\s*(?:0|1|0?\.\d+)\s*)?\)$/';

		return 1 === preg_match( $rgb, $value ) ? $value : $fallback;
	}

	/**
	 * Plain text with tags removed and whitespace trimmed.
	 *
	 * @param mixed $value Untrusted value.
	 * @return string
	 */
	public static function text( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( (string) $value ) );
	}

	/**
	 * A URL restricted to the permitted schemes.
	 *
	 * Relative paths and bare fragments are kept: both are legitimate item
	 * targets and neither carries a scheme to reject.
	 *
	 * @param mixed $value Untrusted value.
	 * @return string Empty string when unusable.
	 */
	public static function url( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = trim( wp_unslash( $value ) );

		if ( '' === $value ) {
			return '';
		}

		return esc_url_raw( $value, self::ALLOWED_SCHEMES );
	}

	/**
	 * A phone number reduced to digits, keeping a single leading plus.
	 *
	 * @param mixed $value Untrusted value.
	 * @return string Empty string when no digits remain.
	 */
	public static function phone( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$value  = trim( (string) $value );
		$plus   = 0 === strpos( $value, '+' ) ? '+' : '';
		$digits = preg_replace( '/[^0-9]/', '', $value );

		return '' === $digits ? '' : $plus . $digits;
	}

	/**
	 * A CSS selector for the element the bar retreats behind.
	 *
	 * Braces, angle brackets and at-rules are rejected outright rather than
	 * stripped: a selector that needed repairing is one to look at again.
	 *
	 * @param mixed $value Untrusted value.
	 * @return string
	 */
	public static function css_selector( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = trim( wp_unslash( $value ) );

		if ( '' === $value || 1 === preg_match( '/[{}<>;@]/', $value ) ) {
			return '';
		}

		return $value;
	}

	/**
	 * Custom CSS with the constructs that can execute script removed.
	 *
	 * Matches are removed rather than the whole value rejected: one mistyped
	 * rule should not silently discard a page of working CSS.
	 *
	 * @param mixed $value Untrusted value.
	 * @return string
	 */
	public static function css( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = wp_strip_all_tags( wp_unslash( $value ) );

		$forbidden = array(
			'#<\s*/?\s*style#i',
			'#@\s*import#i',
			'#javascript\s*:#i',
			'#expression\s*\(#i',
			'#behaviou?r\s*:#i',
			'#-moz-binding#i',
		);

		foreach ( $forbidden as $pattern ) {
			$value = preg_replace( $pattern, '', $value );
		}

		return trim( $value );
	}

	/**
	 * A list of positive integer ids.
	 *
	 * @param mixed $value Untrusted list.
	 * @return int[]
	 */
	public static function id_list( $value ) {
		if ( is_string( $value ) ) {
			$value = preg_split( '/[\s,]+/', $value );
		}

		if ( ! is_array( $value ) ) {
			return array();
		}

		$clean = array();

		foreach ( $value as $id ) {
			if ( is_numeric( $id ) && (int) $id > 0 ) {
				$clean[] = (int) $id;
			}
		}

		return array_values( array_unique( $clean ) );
	}

	/**
	 * A fresh item id.
	 *
	 * @return string
	 */
	public static function item_id() {
		return 'itm_' . substr( str_replace( '-', '', wp_generate_uuid4() ), 0, 8 );
	}

	/**
	 * One item, normalised against its declared type.
	 *
	 * @param mixed $value Untrusted item.
	 * @return array|null Null when the item has no usable type.
	 */
	public static function item( $value ) {
		if ( ! is_array( $value ) ) {
			return null;
		}

		$type_slug = isset( $value['type'] ) ? $value['type'] : '';

		if ( ! FBar_Item_Types::exists( $type_slug ) ) {
			return null;
		}

		$type = FBar_Item_Types::get( $type_slug );
		$show = isset( $value['show'] ) && is_array( $value['show'] ) ? $value['show'] : array();

		$id = isset( $value['id'] ) && is_string( $value['id'] )
			&& 1 === preg_match( '/^itm_[0-9a-f]{8}$/', $value['id'] )
			? $value['id']
			: self::item_id();

		return array(
			'id'      => $id,
			'type'    => $type_slug,
			'label'   => self::text( isset( $value['label'] ) ? $value['label'] : '' ),
			'value'   => self::item_value( isset( $value['value'] ) ? $value['value'] : '', $type['value']['kind'] ),
			'extra'   => self::item_extra( isset( $value['extra'] ) ? $value['extra'] : array(), $type['extra'] ),
			'icon'    => sanitize_key( isset( $value['icon'] ) && '' !== $value['icon'] ? $value['icon'] : $type['icon'] ),
			'primary' => self::boolean( isset( $value['primary'] ) ? $value['primary'] : false ),
			'show'    => array(
				'devices' => self::choice(
					isset( $show['devices'] ) ? $show['devices'] : 'inherit',
					array( 'inherit', 'phone', 'phone_tablet', 'all' ),
					'inherit'
				),
				'users'   => self::choice(
					isset( $show['users'] ) ? $show['users'] : 'inherit',
					array( 'inherit', 'all', 'in', 'out' ),
					'inherit'
				),
			),
		);
	}

	/**
	 * An item's main value, sanitised by the kind its type asks for.
	 *
	 * @param mixed  $value Untrusted value.
	 * @param string $kind  Value kind from the type registry.
	 * @return mixed
	 */
	private static function item_value( $value, $kind ) {
		switch ( $kind ) {
			case 'phone':
				return self::phone( $value );

			case 'email':
				return is_scalar( $value ) ? sanitize_email( (string) $value ) : '';

			case 'url':
				return self::url( $value );

			case 'selector':
				return self::css_selector( $value );

			case 'post':
				$id = is_numeric( $value ) ? (int) $value : 0;
				return ( $id > 0 && get_post( $id ) ) ? $id : 0;

			case 'text':
				return self::text( $value );

			case 'none':
			default:
				return '';
		}
	}

	/**
	 * An item's secondary fields, restricted to those its type declares.
	 *
	 * Undeclared keys are dropped rather than sanitised, so nothing a type does
	 * not know about ever reaches the stored option.
	 *
	 * @param mixed $value    Untrusted extra fields.
	 * @param array $declared Field definitions from the type registry.
	 * @return array
	 */
	private static function item_extra( $value, array $declared ) {
		if ( ! is_array( $value ) || array() === $declared ) {
			return array();
		}

		$clean = array();

		foreach ( $declared as $key => $field ) {
			if ( ! isset( $value[ $key ] ) ) {
				continue;
			}

			$kind = isset( $field['kind'] ) ? $field['kind'] : 'text';

			if ( 'boolean' === $kind ) {
				$clean[ $key ] = self::boolean( $value[ $key ] );
				continue;
			}

			if ( 'choice' === $kind ) {
				$choices       = isset( $field['choices'] ) ? (array) $field['choices'] : array();
				$fallback      = isset( $choices[0] ) ? $choices[0] : '';
				$clean[ $key ] = self::choice( $value[ $key ], $choices, $fallback );
				continue;
			}

			$clean[ $key ] = self::item_value( $value[ $key ], $kind );
		}

		return $clean;
	}

	/**
	 * A list of items, capped and with unique ids.
	 *
	 * @param mixed $value Untrusted list.
	 * @param int   $max   Maximum number of items to keep.
	 * @return array
	 */
	public static function items( $value, $max = 4 ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$clean = array();
		$seen  = array();

		foreach ( $value as $raw ) {
			if ( count( $clean ) >= $max ) {
				break;
			}

			$item = self::item( $raw );

			if ( null === $item ) {
				continue;
			}

			while ( in_array( $item['id'], $seen, true ) ) {
				$item['id'] = self::item_id();
			}

			$seen[]  = $item['id'];
			$clean[] = $item;
		}

		return $clean;
	}

	/**
	 * The whole configuration.
	 *
	 * Never fails and never returns a partial structure. Any input, including
	 * none at all, produces a complete configuration.
	 *
	 * @param mixed $value Untrusted configuration.
	 * @return array
	 */
	public static function settings( $value ) {
		$defaults = FBar_Settings::defaults();

		if ( ! is_array( $value ) ) {
			return $defaults;
		}

		FBar_Settings::flush();

		$display   = self::group( $value, 'display' );
		$content   = self::group( $display, 'content' );
		$behaviour = self::group( $value, 'behaviour' );
		$style     = self::group( $value, 'style' );
		$item      = self::group( $style, 'item' );
		$label     = self::group( $style, 'label' );

		return array(
			'enabled'                 => self::boolean( self::pick( $value, 'enabled', $defaults['enabled'] ) ),

			'display'                 => array(
				'devices'    => self::choice(
					self::pick( $display, 'devices', $defaults['display']['devices'] ),
					array( 'phone', 'phone_tablet', 'all' ),
					$defaults['display']['devices']
				),
				'breakpoint' => self::int_in_range( self::pick( $display, 'breakpoint', 1023 ), 320, 2560, 1023 ),
				'phone_max'  => self::int_in_range( self::pick( $display, 'phone_max', 767 ), 320, 2560, 767 ),
				'content'    => array(
					'mode' => self::choice(
						self::pick( $content, 'mode', 'all' ),
						array( 'all', 'include', 'exclude' ),
						'all'
					),
					'ids'  => self::id_list( self::pick( $content, 'ids', array() ) ),
				),
				'users'      => self::choice(
					self::pick( $display, 'users', 'all' ),
					array( 'all', 'in', 'out' ),
					'all'
				),
			),

			'behaviour'               => array(
				'position'      => self::choice( self::pick( $behaviour, 'position', 'bottom' ), array( 'bottom', 'top' ), 'bottom' ),
				'appear'        => self::choice( self::pick( $behaviour, 'appear', 'always' ), array( 'always', 'scroll_up' ), 'always' ),
				'entrance'      => self::choice( self::pick( $behaviour, 'entrance', 'slide' ), array( 'none', 'slide', 'fade' ), 'slide' ),
				'hide_selector' => self::css_selector( self::pick( $behaviour, 'hide_selector', '' ) ),
				'clearance'     => self::int_in_range( self::pick( $behaviour, 'clearance', 0 ), 0, 400, 0 ),
				'z_index'       => self::int_in_range( self::pick( $behaviour, 'z_index', 9990 ), 1, 2147483647, 9990 ),
			),

			'style'                   => array(
				'layout'     => self::choice( self::pick( $style, 'layout', 'island' ), array( 'island', 'full' ), 'island' ),
				'max_width'  => self::int_in_range( self::pick( $style, 'max_width', 640 ), 0, 2560, 640 ),
				'radius'     => self::int_in_range( self::pick( $style, 'radius', 18 ), 0, 60, 18 ),
				'shadow'     => self::choice( self::pick( $style, 'shadow', 'soft' ), array( 'none', 'soft', 'strong' ), 'soft' ),
				'blur'       => self::boolean( self::pick( $style, 'blur', true ) ),
				'divider'    => self::choice( self::pick( $style, 'divider', 'hairline' ), array( 'none', 'hairline' ), 'hairline' ),
				'gap'        => self::int_in_range( self::pick( $style, 'gap', 11 ), 0, 40, 11 ),
				'item'       => array(
					'shape'      => self::choice( self::pick( $item, 'shape', 'plain' ), array( 'plain', 'filled', 'outline', 'soft' ), 'plain' ),
					'radius'     => self::int_in_range( self::pick( $item, 'radius', 13 ), 0, 60, 13 ),
					'min_height' => self::int_in_range( self::pick( $item, 'min_height', 52 ), 28, 120, 52 ),
					'icon_size'  => self::int_in_range( self::pick( $item, 'icon_size', 18 ), 10, 48, 18 ),
					'icon_gap'   => self::int_in_range( self::pick( $item, 'icon_gap', 3 ), 0, 20, 3 ),
				),
				'label'      => array(
					'show' => self::boolean( self::pick( $label, 'show', true ) ),
					'size' => self::int_in_range( self::pick( $label, 'size', 10 ), 7, 20, 10 ),
					'case' => self::choice( self::pick( $label, 'case', 'upper' ), array( 'upper', 'normal' ), 'upper' ),
				),
				'scheme'     => self::choice( self::pick( $style, 'scheme', 'system' ), array( 'system', 'light', 'dark', 'off' ), 'system' ),
				'light'      => self::palette( self::group( $style, 'light' ), $defaults['style']['light'] ),
				'dark'       => self::palette( self::group( $style, 'dark' ), $defaults['style']['dark'] ),
				'custom_css' => self::css( self::pick( $style, 'custom_css', '' ) ),
			),

			'items'                   => self::items( self::pick( $value, 'items', array() ) ),

			'keep_settings_on_delete' => self::boolean( self::pick( $value, 'keep_settings_on_delete', false ) ),
		);
	}

	/**
	 * One value from an untrusted array, or a fallback.
	 *
	 * @param array  $source   Untrusted array.
	 * @param string $key      Key to read.
	 * @param mixed  $fallback Value when the key is absent.
	 * @return mixed
	 */
	private static function pick( array $source, $key, $fallback ) {
		return array_key_exists( $key, $source ) ? $source[ $key ] : $fallback;
	}

	/**
	 * One nested group from an untrusted array, always as an array.
	 *
	 * @param array  $source Untrusted array.
	 * @param string $key    Key to read.
	 * @return array
	 */
	private static function group( array $source, $key ) {
		return ( isset( $source[ $key ] ) && is_array( $source[ $key ] ) ) ? $source[ $key ] : array();
	}

	/**
	 * A colour palette, keyed exactly as its defaults.
	 *
	 * Iterating the defaults rather than the input guarantees both palettes
	 * always carry identical tokens. A token present in one and missing from
	 * the other produces a colour whose only definition sits inside a
	 * preference query, which then never applies in the unstamped state.
	 *
	 * @param array $value    Untrusted palette.
	 * @param array $defaults Default palette.
	 * @return array
	 */
	private static function palette( array $value, array $defaults ) {
		$clean = array();

		foreach ( $defaults as $token => $default ) {
			$clean[ $token ] = self::color( self::pick( $value, $token, $default ), $default );
		}

		return $clean;
	}
}
