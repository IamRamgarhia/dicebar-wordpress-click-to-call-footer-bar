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

		$css = self::variables( $settings ) . self::device_queries( $settings );

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
	 * The device media queries.
	 *
	 * Two bands, from three settings: a phone is up to phone_max, a tablet up
	 * to breakpoint, a desktop anything above it. Every rule below is one of
	 * those two edges, written as a min-width where the thing should stop
	 * showing or a max-width where it should not start.
	 *
	 * Written out per rule rather than generated from shows_on() in a loop,
	 * because the result is a stylesheet a person may well read in their
	 * browser's inspector while working out why a button is not where they
	 * expected it, and a loop would emit the same rules in a less readable
	 * order.
	 *
	 * @param array $settings The configuration.
	 * @return string
	 */
	private static function device_queries( array $settings ) {
		$breakpoint = (int) $settings['display']['breakpoint'];
		$phone_max  = (int) $settings['display']['phone_max'];

		$wider_than_phone  = '@media (min-width:' . ( $phone_max + 1 ) . 'px){';
		$wider_than_tablet = '@media (min-width:' . ( $breakpoint + 1 ) . 'px){';
		$phone_only        = '@media (max-width:' . $phone_max . 'px){';
		$upto_tablet       = '@media (max-width:' . $breakpoint . 'px){';

		$css = '';

		// --- Rules that stop showing as the screen gets wider. ------------

		$css .= $wider_than_tablet
			. '.dicebar--fixed.dicebar--device-phone_tablet{display:none}'
			. '.dicebar__item--device-phone_tablet{display:none}'
			. '.dicebar--fixed.dicebar--device-tablet{display:none}'
			. '.dicebar__item--device-tablet{display:none}'
			. '}';

		$css .= $wider_than_phone
			. '.dicebar--fixed.dicebar--device-phone{display:none}'
			. '.dicebar__item--device-phone{display:none}'
			. '}';

		// --- Rules that do not start showing until it is wider. -----------
		//
		// The mirror of the block above, and what makes "one more button on a
		// desktop" expressible at all.

		$css .= $phone_only
			. '.dicebar--fixed.dicebar--device-tablet{display:none}'
			. '.dicebar__item--device-tablet{display:none}'
			. '.dicebar--fixed.dicebar--device-tablet_up{display:none}'
			. '.dicebar__item--device-tablet_up{display:none}'
			. '.dicebar--fixed.dicebar--device-desktop{display:none}'
			. '.dicebar__item--device-desktop{display:none}'
			. '}';

		$css .= $upto_tablet
			. '.dicebar--fixed.dicebar--device-desktop{display:none}'
			. '.dicebar__item--device-desktop{display:none}'
			. '}';

		$css .= self::label_queries( $settings, $phone_max, $breakpoint );

		return $css;
	}

	/**
	 * Hide the labels at the widths where too many items show to fit them.
	 *
	 * Labels used to be a single yes or no, decided by the item count, which
	 * worked while every item showed on every screen. Once an item can be
	 * desktop-only the count is different on each screen -- three on a phone
	 * and six on a desktop is the whole point of the feature -- so the answer
	 * has to be per width as well.
	 *
	 * DiceBar_Render emits the label markup whenever labels fit on ANY screen
	 * and adds an aria-label alongside it, so an item whose label is hidden
	 * here still has an accessible name. That is why this can be done in CSS
	 * at all: display:none takes the label out of the accessibility tree, and
	 * the aria-label is what is left.
	 *
	 * @param array $settings   The configuration.
	 * @param int   $phone_max  Widest phone.
	 * @param int   $breakpoint Widest tablet.
	 * @return string
	 */
	private static function label_queries( array $settings, $phone_max, $breakpoint ) {
		$fits = DiceBar_Render::labels_fit_per_screen( $settings );

		// All three the same needs no query: Render showed or dropped the
		// labels outright.
		if ( count( array_unique( $fits ) ) < 2 ) {
			return '';
		}

		// The space matters: the label is inside the bar, not the bar itself.
		// Without it this reads as one element carrying both classes, which
		// matches nothing, and the labels this is meant to hide stay on
		// screen and clip -- the exact failure the whole per-screen
		// arithmetic exists to prevent.
		$hide  = ' .dicebar__label{display:none}';
		$edges = array(
			'phone'   => '@media (max-width:' . $phone_max . 'px){',
			'tablet'  => '@media (min-width:' . ( $phone_max + 1 ) . 'px) and (max-width:' . $breakpoint . 'px){',
			'desktop' => '@media (min-width:' . ( $breakpoint + 1 ) . 'px){',
		);

		$css = '';

		foreach ( $edges as $screen => $query ) {
			if ( empty( $fits[ $screen ] ) ) {
				$css .= $query . '.dicebar' . $hide . '}';
			}
		}

		return $css;
	}
}
