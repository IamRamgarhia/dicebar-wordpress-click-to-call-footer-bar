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
 * @package FooterBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the inline style block that accompanies the bar.
 */
class FBar_Styles {

	/**
	 * The complete inline style element, escaped and ready to echo.
	 *
	 * @return string
	 */
	public static function inline_block() {
		$settings = FBar_Settings::get();

		$css = self::variables( $settings )
			. self::device_queries( $settings )
			. self::custom( $settings );

		if ( '' === trim( $css ) ) {
			return '';
		}

		// wp_strip_all_tags on the whole block is the last line of defence: no
		// value reaching here can close the style element and start a script.
		return '<style id="fbar-inline">' . wp_strip_all_tags( $css ) . '</style>';
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
			'--fbar-radius'      => (int) $style['radius'] . 'px',
			'--fbar-item-radius' => (int) $style['item']['radius'] . 'px',
			'--fbar-min-height'  => (int) $style['item']['min_height'] . 'px',
			'--fbar-icon-size'   => (int) $style['item']['icon_size'] . 'px',
			'--fbar-icon-gap'    => (int) $style['item']['icon_gap'] . 'px',
			'--fbar-gap'         => (int) $style['gap'] . 'px',
			'--fbar-label-size'  => (int) $style['label']['size'] . 'px',
			'--fbar-max-width'   => $style['max_width'] ? (int) $style['max_width'] . 'px' : 'none',
			'--fbar-z'           => (int) $settings['behaviour']['z_index'],
			'--fbar-clearance'   => (int) $settings['behaviour']['clearance'] . 'px',
			'--fbar-glass'       => (int) $style['glass'] . 'px',
			'--fbar-opacity'     => (int) $style['opacity'] . '%',
		);

		$css = '.fbar{' . self::declarations( $base ) . self::declarations( self::palette( $style['light'] ) ) . '}';

		$dark = self::declarations( self::palette( $style['dark'] ) );

		if ( 'off' !== $style['scheme'] ) {
			$css .= '@media (prefers-color-scheme: dark){.fbar:not(.fbar--scheme-light){' . $dark . '}}';
			$css .= '.fbar--scheme-dark{' . $dark . '}';
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
			$properties[ '--fbar-' . str_replace( '_', '-', $token ) ] = $value;
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
			. '.fbar--fixed.fbar--device-phone_tablet{display:none}'
			. '}';

		$css .= '@media (min-width:' . ( $phone_max + 1 ) . 'px){'
			. '.fbar--fixed.fbar--device-phone{display:none}'
			. '.fbar__item--device-phone{display:none}'
			. '}';

		$css .= '@media (min-width:' . ( $breakpoint + 1 ) . 'px){'
			. '.fbar__item--device-phone_tablet{display:none}'
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
