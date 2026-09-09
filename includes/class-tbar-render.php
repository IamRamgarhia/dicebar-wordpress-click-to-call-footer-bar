<?php
/**
 * Front-end markup.
 *
 * One renderer, two modes. Fixed mode prints on wp_footer and sits pinned to
 * the viewport. Inline mode prints wherever the owner puts it, with the fixed
 * positioning, blur, shadow and body offset all removed.
 *
 * @package TapBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the bar's markup and decides whether it should exist at all.
 */
class TBar_Render {

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

		$settings = TBar_Settings::get();

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
		return (bool) apply_filters( 'tbar_will_render', true, $settings );
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

		// Only the inline style block and the markup are echoed, and both are
		// assembled from escaped parts by the methods that build them.
		echo TBar_Styles::inline_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::bar( TBar_Settings::get(), 'fixed' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
			'tbar',
			'tbar--' . ( 'inline' === $mode ? 'inline' : 'fixed' ),
			'tbar--' . $settings['behaviour']['position'],
			'tbar--' . $settings['style']['layout'],
			'tbar--item-' . $settings['style']['item']['shape'],
			'tbar--shadow-' . $settings['style']['shadow'],
			$settings['style']['label']['show'] ? 'tbar--labelled' : 'tbar--unlabelled',
			$settings['style']['blur'] ? 'tbar--blur' : 'tbar--no-blur',
			'tbar--divider-' . $settings['style']['divider'],
			'tbar--case-' . $settings['style']['label']['case'],
			'tbar--count-' . count( $items ),
		);

		if ( 'fixed' === $mode ) {
			$classes[] = 'tbar--device-' . $settings['display']['devices'];
			$classes[] = 'tbar--appear-' . $settings['behaviour']['appear'];
			$classes[] = 'tbar--entrance-' . $settings['behaviour']['entrance'];

			if ( is_admin_bar_showing() ) {
				$classes[] = 'tbar--admin-bar';
			}
		}

		if ( 'system' !== $settings['style']['scheme'] ) {
			$classes[] = 'tbar--scheme-' . $settings['style']['scheme'];
		}

		$markup = '';

		foreach ( $items as $item ) {
			$markup .= self::item( $item, $settings );
		}

		return sprintf(
			'<nav class="%1$s" aria-label="%2$s" data-tbar-mode="%3$s"%4$s><div class="tbar__inner">%5$s</div></nav>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr__( 'Quick actions', 'tapbar-mobile-action-bar' ),
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
			'data-tbar-users'     => $settings['display']['users'],
			'data-tbar-appear'    => $settings['behaviour']['appear'],
			'data-tbar-clearance' => (string) $settings['behaviour']['clearance'],
		);

		if ( '' !== $settings['behaviour']['hide_selector'] ) {
			$attributes['data-tbar-hide-near'] = $settings['behaviour']['hide_selector'];
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

			$href = TBar_Item_Types::href( $item );

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
	 * One item.
	 *
	 * @param array $item     Sanitised item with its href resolved.
	 * @param array $settings The configuration.
	 * @return string
	 */
	private static function item( array $item, array $settings ) {
		$label      = '' !== $item['label'] ? $item['label'] : self::default_label( $item );
		$show_label = ! empty( $settings['style']['label']['show'] );
		$icon       = TBar_Icons::render( $item['icon'] );

		$classes = array( 'tbar__item' );

		if ( ! empty( $item['primary'] ) ) {
			$classes[] = 'tbar__item--primary';
		}

		if ( 'inherit' !== $item['show']['devices'] ) {
			$classes[] = 'tbar__item--device-' . $item['show']['devices'];
		}

		$inner = $icon;

		if ( $show_label ) {
			$inner .= '<span class="tbar__label">' . esc_html( $label ) . '</span>';
		}

		$attributes = sprintf(
			'class="%s" data-tbar-item="%s" data-tbar-type="%s"',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $item['id'] ),
			esc_attr( $item['type'] )
		);

		// An icon-only item has no visible text, so it needs an accessible
		// name. A labelled one already has one and must not have both, or a
		// screen reader announces it twice.
		if ( ! $show_label ) {
			$attributes .= sprintf( ' aria-label="%s"', esc_attr( $label ) );
		}

		if ( 'inherit' !== $item['show']['users'] ) {
			$attributes .= sprintf( ' data-tbar-users="%s"', esc_attr( $item['show']['users'] ) );
		}

		if ( in_array( $item['type'], array( 'top', 'share', 'anchor' ), true ) ) {
			$attributes .= sprintf( ' data-tbar-action="%s"', esc_attr( $item['type'] ) );
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
			esc_url( $item['href'], TBar_Sanitize::ALLOWED_SCHEMES ),
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
		$type = TBar_Item_Types::get( $item['type'] );

		return $type ? $type['label'] : '';
	}
}
