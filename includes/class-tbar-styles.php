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
 * @package TapBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the inline style block that accompanies the bar.
 */
class TBar_Styles {

	/**
	 * The complete inline style element, escaped and ready to echo.
	 *
	 * @return string
	 */
	public static function inline_block() {
		$settings = TBar_Settings::get();

		$css = self::variables( $settings )
			. self::device_queries( $settings )
			. self::custom( $settings );

		if ( '' === trim( $css ) ) {
			return '';
		}

		// wp_strip_all_tags on the whole block is the last line of defence: no
		// value reaching here can close the style element and start a script.
		return '<style id="tbar-inline">' . wp_strip_all_tags( $css ) . '</style>';
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
			'--tbar-radius'      => (int) $style['radius'] . 'px',
			'--tbar-item-radius' => (int) $style['item']['radius'] . 'px',
			'--tbar-min-height'  => (int) $style['item']['min_height'] . 'px',
			'--tbar-icon-size'   => (int) $style['item']['icon_size'] . 'px',
			'--tbar-icon-gap'    => (int) $style['item']['icon_gap'] . 'px',
			'--tbar-gap'         => (int) $style['gap'] . 'px',
			'--tbar-label-size'  => (int) $style['label']['size'] . 'px',
			'--tbar-max-width'   => $style['max_width'] ? (int) $style['max_width'] . 'px' : 'none',
			'--tbar-z'           => (int) $settings['behaviour']['z_index'],
			'--tbar-clearance'   => (int) $settings['behaviour']['clearance'] . 'px',
		);

		$css = '.tbar{' . self::declarations( $base ) . self::declarations( self::palette( $style['light'] ) ) . '}';

		$dark = self::declarations( self::palette( $style['dark'] ) );

		if ( 'off' !== $style['scheme'] ) {
			$css .= '@media (prefers-color-scheme: dark){.tbar:not(.tbar--scheme-light){' . $dark . '}}';
			$css .= '.tbar--scheme-dark{' . $dark . '}';
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
			$properties[ '--tbar-' . str_replace( '_', '-', $token ) ] = $value;
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
			. '.tbar--fixed.tbar--device-phone_tablet{display:none}'
			. '}';

		$css .= '@media (min-width:' . ( $phone_max + 1 ) . 'px){'
			. '.tbar--fixed.tbar--device-phone{display:none}'
			. '.tbar__item--device-phone{display:none}'
			. '}';

		$css .= '@media (min-width:' . ( $breakpoint + 1 ) . 'px){'
			. '.tbar__item--device-phone_tablet{display:none}'
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
