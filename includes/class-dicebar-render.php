<?php
/**
 * Front-end markup.
 *
 * One renderer, two modes. Fixed mode prints on wp_footer and sits pinned to
 * the viewport. Inline mode prints wherever the owner puts it, with the fixed
 * positioning, blur, shadow and body offset all removed.
 *
 * @package DiceBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the bar's markup and decides whether it should exist at all.
 */
class DiceBar_Render {

	/**
	 * Whether the fixed bar should render on this request.
	 *
	 * Only rules that vary with the URL are evaluated here. A page cache keys
	 * on the URL, so these results cache correctly. Rules that vary within one
	 * URL, such as device width, are handled in CSS instead.
	 *
	 * @return bool
	 */
	public static function will_render() {
		if ( is_admin() || wp_doing_ajax() || is_feed() || is_embed() ) {
			return false;
		}

		// The AMP plugin rejects custom script outright, so render nothing
		// rather than markup that fails its validation.
		if ( function_exists( 'amp_is_request' ) && amp_is_request() ) {
			return false;
		}

		$settings = DiceBar_Settings::get();

		if ( empty( $settings['enabled'] ) || empty( $settings['items'] ) ) {
			return false;
		}

		if ( ! self::passes_content_rules( $settings ) ) {
			return false;
		}

		/**
		 * Filters whether the fixed bar renders on this request.
		 *
		 * @param bool  $render   Whether to render.
		 * @param array $settings The configuration.
		 */
		return (bool) apply_filters( 'dicebar_will_render', true, $settings );
	}

	/**
	 * Whether this URL passes the include or exclude list.
	 *
	 * @param array $settings The configuration.
	 * @return bool
	 */
	private static function passes_content_rules( array $settings ) {
		$content = $settings['display']['content'];

		if ( 'all' === $content['mode'] || empty( $content['ids'] ) ) {
			return true;
		}

		$id     = is_singular() ? get_queried_object_id() : 0;
		$listed = in_array( (int) $id, array_map( 'intval', $content['ids'] ), true );

		return 'include' === $content['mode'] ? $listed : ! $listed;
	}

	/**
	 * Print the fixed bar. Hooked to wp_footer.
	 *
	 * @return void
	 */
	public static function footer() {
		if ( ! self::will_render() ) {
			return;
		}

		// The stylesheet is attached through wp_add_inline_style() at enqueue
		// time. Only the markup is printed here, and every value in it was
		// escaped by the method that built it.
		echo self::bar( DiceBar_Settings::get(), 'fixed' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * The bar's markup.
	 *
	 * @param array  $settings The configuration.
	 * @param string $mode     Either fixed or inline.
	 * @param array  $only     Item ids to include. Empty means every item.
	 * @return string
	 */
	public static function bar( array $settings, $mode = 'fixed', array $only = array() ) {
		$items = self::visible_items( $settings, $only );

		if ( ! $items ) {
			return '';
		}

		$classes = array(
			'dicebar',
			'dicebar--' . ( 'inline' === $mode ? 'inline' : 'fixed' ),
			'dicebar--' . $settings['behaviour']['position'],
			'dicebar--' . $settings['style']['layout'],
			'dicebar--item-' . $settings['style']['item']['shape'],
			'dicebar--shadow-' . $settings['style']['shadow'],
			'dicebar--show-' . str_replace( '_', '-', $settings['style']['label']['mode'] ),
			'dicebar--preset-' . $settings['style']['preset'],
			$settings['style']['blur'] ? 'dicebar--blur' : 'dicebar--no-blur',
			'dicebar--divider-' . $settings['style']['divider'],
			'dicebar--case-' . $settings['style']['label']['case'],
			'dicebar--count-' . count( $items ),
		);

		if ( 'fixed' === $mode ) {
			$classes[] = 'dicebar--device-' . $settings['display']['devices'];
			$classes[] = 'dicebar--appear-' . $settings['behaviour']['appear'];
			$classes[] = 'dicebar--entrance-' . $settings['behaviour']['entrance'];

			if ( is_admin_bar_showing() ) {
				$classes[] = 'dicebar--admin-bar';
			}
		}

		if ( 'system' !== $settings['style']['scheme'] ) {
			$classes[] = 'dicebar--scheme-' . $settings['style']['scheme'];
		}

		$markup = '';

		// Past four items a label has nowhere to go: at 320 pixels wide it is
		// cut short rather than shrinking. So the bar drops to icons only,
		// whatever the label setting says.
		//
		// Asked per screen, because an item can now be desktop-only: three
		// buttons on a phone and six on a desktop is the point of that, and a
		// single yes-or-no would have dropped the phone's labels because of
		// items the phone never shows.
		//
		// Fitting everywhere means labels and no aria-label: the visible text
		// is the accessible name. Fitting nowhere means no labels and an
		// aria-label on every item.
		//
		// Fitting on some screens and not others means both: the labels go in
		// the markup, DiceBar_Styles hides them at the widths where they do
		// not fit, and the aria-label is what is left there -- display:none
		// takes a label out of the accessibility tree as well as off the
		// screen, so without it those items would have no name at exactly
		// those widths.
		$fits        = self::labels_fit_per_screen( $settings );
		$fits_any    = (bool) array_filter( $fits );
		$fits_all    = ! in_array( false, $fits, true );
		$name_needed = ! $fits_all;

		foreach ( $items as $item ) {
			$markup .= self::item( $item, $settings, $fits_any, $name_needed );
		}

		return sprintf(
			'<nav class="%1$s" aria-label="%2$s" data-dicebar-mode="%3$s"%4$s><div class="dicebar__inner">%5$s</div></nav>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr__( 'Quick actions', 'dicebar' ),
			esc_attr( $mode ),
			'fixed' === $mode ? ' ' . self::fixed_attributes( $settings ) : '',
			$markup
		);
	}

	/**
	 * Data attributes the front-end script reads on the fixed bar.
	 *
	 * @param array $settings The configuration.
	 * @return string
	 */
	private static function fixed_attributes( array $settings ) {
		$attributes = array(
			'data-dicebar-users'     => $settings['display']['users'],
			'data-dicebar-appear'    => $settings['behaviour']['appear'],
			'data-dicebar-clearance' => (string) $settings['behaviour']['clearance'],
		);

		if ( '' !== $settings['behaviour']['hide_selector'] ) {
			$attributes['data-dicebar-hide-near'] = $settings['behaviour']['hide_selector'];
		}

		$out = array();

		foreach ( $attributes as $name => $value ) {
			$out[] = sprintf( '%s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}

		return implode( ' ', $out );
	}

	/**
	 * Items that survive the URL-scoped rules.
	 *
	 * Per-item device and user rules are left to CSS and to the script, so an
	 * item hidden for one visitor is still present for a cached page served to
	 * another.
	 *
	 * @param array $settings The configuration.
	 * @param array $only     Item ids to include. Empty means every item.
	 * @return array
	 */
	private static function visible_items( array $settings, array $only = array() ) {
		$items = array();

		foreach ( $settings['items'] as $item ) {
			if ( $only && ! in_array( $item['id'], $only, true ) ) {
				continue;
			}

			$href = DiceBar_Item_Types::href( $item );

			// A link type with nothing to link to is a gap in the row rather
			// than a button, so it is dropped instead of rendered dead.
			if ( '' === $href && ! in_array( $item['type'], array( 'top', 'share', 'text' ), true ) ) {
				continue;
			}

			$item['href'] = $href;
			$items[]      = $item;
		}

		return $items;
	}

	/**
	 * How many items show on each screen.
	 *
	 * An item's own rule wins where it has one; `inherit` means it shows
	 * wherever the bar does. Both are measured against the same
	 * DiceBar_Sanitize::shows_on(), so this cannot drift from the stylesheet.
	 *
	 * @param array $settings The configuration.
	 * @return array Screen name => how many items show there.
	 */
	public static function items_per_screen( array $settings ) {
		$items = self::visible_items( $settings );
		$bar   = $settings['display']['devices'];
		$out   = array();

		foreach ( DiceBar_Sanitize::SCREENS as $screen ) {
			$count = 0;

			foreach ( $items as $item ) {
				$rule = $item['show']['devices'];

				if ( 'inherit' === $rule ) {
					$rule = $bar;
				} elseif ( ! DiceBar_Sanitize::shows_on( $bar, $screen ) ) {
					// The bar is not on this screen, so nothing on it is,
					// whatever the item asked for.
					continue;
				}

				if ( DiceBar_Sanitize::shows_on( $rule, $screen ) ) {
					++$count;
				}
			}

			$out[ $screen ] = $count;
		}

		return $out;
	}

	/**
	 * Whether labels fit on each screen that shows anything.
	 *
	 * Screens showing no items are left out rather than counted as fitting.
	 * Counting them was the first version, and it was wrong in a way the
	 * tests caught: a bar set to phones and tablets shows nothing on a
	 * desktop, that empty desktop answered "the labels fit here", and five
	 * items on a phone kept their labels on the strength of a screen the bar
	 * never appears on.
	 *
	 * Leaving the screen out instead means no label rule is written for it,
	 * which is right -- the bar is not there to need one.
	 *
	 * @param array $settings The configuration.
	 * @return array Screen name => bool, for the screens that show items.
	 */
	public static function labels_fit_per_screen( array $settings ) {
		$out = array();

		foreach ( self::items_per_screen( $settings ) as $screen => $count ) {
			if ( $count < 1 ) {
				continue;
			}

			$out[ $screen ] = $count <= DiceBar_Sanitize::LABELS_FIT;
		}

		return $out;
	}

	/**
	 * One item.
	 *
	 * @param array $item        Sanitised item with its href resolved.
	 * @param array $settings The configuration.
	 * @param bool  $labels_fit  Whether labels fit on any screen this bar shows on.
	 * @param bool  $name_needed Whether the label is hidden at some widths, so the
	 *                           item needs an aria-label as well as its text.
	 * @return string
	 */
	private static function item( array $item, array $settings, $labels_fit = true, $name_needed = false ) {
		$label      = '' !== $item['label'] ? $item['label'] : self::default_label( $item );
		$mode       = $settings['style']['label']['mode'];
		$show_label = 'icon' !== $mode && $labels_fit;

		// An icon is what is left when the label goes, so a bar set to labels
		// only still has something to show once there are too many of them.
		// Kept on whenever the label might be hidden at some width, or that
		// bar would be empty on a desktop and full on a phone.
		$show_icon = 'label' !== $mode || ! $labels_fit || $name_needed;
		$icon      = $show_icon ? DiceBar_Icons::render( $item['icon'] ) : '';

		$classes = array( 'dicebar__item' );

		if ( ! empty( $item['primary'] ) ) {
			$classes[] = 'dicebar__item--primary';
		}

		if ( 'inherit' !== $item['show']['devices'] ) {
			$classes[] = 'dicebar__item--device-' . $item['show']['devices'];
		}

		// A network's own colour, when the owner asked for it. Set as a
		// property rather than on the glyph so the hover state can still
		// take the colour back.
		$brand = '';

		if ( ! empty( $settings['style']['brand_icons'] ) ) {
			$brand = DiceBar_Icons::brand_color( $item['icon'] );
		}

		if ( '' !== $brand ) {
			$classes[] = 'dicebar__item--brand';
		}

		$inner = $icon;

		if ( $show_label ) {
			$inner .= '<span class="dicebar__label">' . esc_html( $label ) . '</span>';
		}

		$attributes = sprintf(
			'class="%s" data-dicebar-item="%s" data-dicebar-type="%s"',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $item['id'] ),
			esc_attr( $item['type'] )
		);

		if ( '' !== $brand ) {
			$attributes .= sprintf( ' style="--dicebar-brand:%s"', esc_attr( $brand ) );
		}

		// An icon-only item has no visible text, so it needs an accessible
		// name of its own.
		//
		// A labelled item normally takes its name from that text and gets no
		// aria-label, because two sources of the same name is a smell even
		// where it is harmless. The exception is a label the stylesheet hides
		// at some widths: there, the visible text is gone from the
		// accessibility tree on those screens and the aria-label is the only
		// name left. It carries exactly the label's own words, which is what
		// WCAG 2.5.3 asks of a name that sits over visible text -- and
		// aria-label replaces the name rather than adding to it, so nothing
		// is announced twice.
		if ( ! $show_label || $name_needed ) {
			$attributes .= sprintf( ' aria-label="%s"', esc_attr( $label ) );
		}

		if ( 'inherit' !== $item['show']['users'] ) {
			$attributes .= sprintf( ' data-dicebar-users="%s"', esc_attr( $item['show']['users'] ) );
		}

		if ( in_array( $item['type'], array( 'top', 'share', 'anchor' ), true ) ) {
			$attributes .= sprintf( ' data-dicebar-action="%s"', esc_attr( $item['type'] ) );
		}

		if ( 'text' === $item['type'] ) {
			return sprintf( '<span %s>%s</span>', $attributes, $inner );
		}

		if ( '' === $item['href'] ) {
			return sprintf( '<button type="button" %s>%s</button>', $attributes, $inner );
		}

		$rel = array();

		if ( ! empty( $item['extra']['new_tab'] ) ) {
			$attributes .= ' target="_blank"';
			// noopener is a security property, not a preference, so it is
			// unconditional on any new-tab link.
			$rel[] = 'noopener';
			$rel[] = 'noreferrer';
		}

		if ( ! empty( $item['extra']['nofollow'] ) ) {
			$rel[] = 'nofollow';
		}

		if ( $rel ) {
			$attributes .= sprintf( ' rel="%s"', esc_attr( implode( ' ', $rel ) ) );
		}

		return sprintf(
			'<a href="%s" %s>%s</a>',
			esc_url( $item['href'], DiceBar_Sanitize::ALLOWED_SCHEMES ),
			$attributes,
			$inner
		);
	}

	/**
	 * The label to use when the administrator left the field empty.
	 *
	 * @param array $item Sanitised item.
	 * @return string
	 */
	private static function default_label( array $item ) {
		$type = DiceBar_Item_Types::get( $item['type'] );

		return $type ? $type['label'] : '';
	}
}
