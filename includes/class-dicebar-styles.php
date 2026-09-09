<?php
/**
 * Turns settings into CSS custom properties and media queries.
 *
 * Everything the administrator can change is exposed as a custom property on
 * the wrapper, so any theme can override it and the plugin never ships an
 * !important.
 *
 * The device rules are the exception. Media queries cannot read custom
 * properties, and the breakpoints are configurable, so those two queries are
 * written out here with the saved pixel values baked in.
 *
 * @package DiceBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the inline style block that accompanies the bar.
 */
class DiceBar_Styles {

	/**
	 * The settings-driven CSS, as a plain stylesheet with no tags.
	 *
	 * Handed to wp_add_inline_style() rather than printed inside a style
	 * element. Printing raw tags is a documented review finding, and going
	 * through the enqueue system also means a caching or optimising plugin
	 * sees this CSS the way it sees every other stylesheet.
	 *
	 * @return string
	 */
	public static function css() {
		$settings = DiceBar_Settings::get();

		$css = self::variables( $settings )
			. self::device_queries( $settings )
			. self::custom( $settings );

		// Stripping tags across the whole sheet is the last line of defence:
		// no value reaching here can close a style element and open a script.
		return wp_strip_all_tags( $css );
	}

	/**
	 * Custom properties for both colour schemes.
	 *
	 * The light palette is defined on the bare selector, so it applies in the
	 * unstamped state. The dark tokens are redefined inside the preference
	 * query and again under an explicit class, so a colour never has its only
	 * definition inside a media query.
	 *
	 * @param array $settings The configuration.
	 * @return string
	 */
	private static function variables( array $settings ) {
		$style = $settings['style'];

		$base = array(
			'--dicebar-radius'      => (int) $style['radius'] . 'px',
			'--dicebar-item-radius' => (int) $style['item']['radius'] . 'px',
			'--dicebar-min-height'  => (int) $style['item']['min_height'] . 'px',
			'--dicebar-icon-size'   => (int) $style['item']['icon_size'] . 'px',
			'--dicebar-icon-gap'    => (int) $style['item']['icon_gap'] . 'px',
			'--dicebar-gap'         => (int) $style['gap'] . 'px',
			'--dicebar-label-size'  => (int) $style['label']['size'] . 'px',
			'--dicebar-max-width'   => $style['max_width'] ? (int) $style['max_width'] . 'px' : 'none',
			'--dicebar-z'           => (int) $settings['behaviour']['z_index'],
			'--dicebar-clearance'   => (int) $settings['behaviour']['clearance'] . 'px',
			'--dicebar-glass'       => (int) $style['glass'] . 'px',
			'--dicebar-opacity'     => (int) $style['opacity'] . '%',
		);

		$css = '.dicebar{' . self::declarations( $base ) . self::declarations( self::palette( $style['light'] ) ) . '}';

		$dark = self::declarations( self::palette( $style['dark'] ) );

		if ( 'off' !== $style['scheme'] ) {
			$css .= '@media (prefers-color-scheme: dark){.dicebar:not(.dicebar--scheme-light){' . $dark . '}}';
			$css .= '.dicebar--scheme-dark{' . $dark . '}';
		}

		return $css;
	}

	/**
	 * Palette tokens as custom properties.
	 *
	 * @param array $palette One colour palette.
	 * @return array
	 */
	private static function palette( array $palette ) {
		$properties = array();

		foreach ( $palette as $token => $value ) {
			$properties[ '--dicebar-' . str_replace( '_', '-', $token ) ] = $value;
		}

		return $properties;
	}

	/**
	 * Property declarations, with every value escaped for an attribute.
	 *
	 * @param array $properties Property names mapped to values.
	 * @return string
	 */
	private static function declarations( array $properties ) {
		$out = '';

		foreach ( $properties as $name => $value ) {
			// Values arrive already restricted by the sanitiser: colours match
			// a colour pattern and everything else is cast to an integer by
			// the caller, so neither can carry a semicolon or a closing brace.
			$out .= esc_attr( $name ) . ':' . esc_attr( $value ) . ';';
		}

		return $out;
	}

	/**
	 * The two device media queries.
	 *
	 * @param array $settings The configuration.
	 * @return string
	 */
	private static function device_queries( array $settings ) {
		$breakpoint = (int) $settings['display']['breakpoint'];
		$phone_max  = (int) $settings['display']['phone_max'];

		$css = '';

		// The bar itself, by its device setting.
		$css .= '@media (min-width:' . ( $breakpoint + 1 ) . 'px){'
			. '.dicebar--fixed.dicebar--device-phone_tablet{display:none}'
			. '}';

		$css .= '@media (min-width:' . ( $phone_max + 1 ) . 'px){'
			. '.dicebar--fixed.dicebar--device-phone{display:none}'
			. '.dicebar__item--device-phone{display:none}'
			. '}';

		$css .= '@media (min-width:' . ( $breakpoint + 1 ) . 'px){'
			. '.dicebar__item--device-phone_tablet{display:none}'
			. '}';

		return $css;
	}

	/**
	 * The administrator's own CSS, already filtered by the sanitiser.
	 *
	 * @param array $settings The configuration.
	 * @return string
	 */
	private static function custom( array $settings ) {
		return $settings['style']['custom_css'];
	}
}
