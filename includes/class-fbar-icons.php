<?php
/**
 * The inline SVG icon set.
 *
 * Drawn in-house on a 24 unit grid with a 2 unit stroke, so every glyph reads
 * at the same weight at 18 pixels. Inline rather than a sprite file because
 * the bar renders at most four of them and a request for a sprite costs more
 * than the markup it saves.
 *
 * @package FooterBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Supplies icon markup by name.
 */
class FBar_Icons {

	/**
	 * Path data for each icon, as one or more SVG child elements.
	 *
	 * @return array
	 */
	public static function all() {
		$icons = array(
			'phone'      => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.1a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/>',
			'message'    => '<path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.5 8.5 0 0 1-3.9-.9L3 20.5l1.5-5.1a8.5 8.5 0 0 1-.9-3.9 8.4 8.4 0 0 1 8.4-9 8.4 8.4 0 0 1 9 9Z"/>',
			'mail'       => '<path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="m22 6-10 7L2 6"/>',
			'link'       => '<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7L12.3 19"/>',
			'arrow-down' => '<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>',
			'arrow-up'   => '<path d="M12 19V5"/><path d="m5 12 7-7 7 7"/>',
			'file'       => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>',
			'map-pin'    => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
			'share'      => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4"/><path d="m15.4 6.5-6.8 4"/>',
			'star'       => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.2-6.2 3.2L7 14.2l-5-4.9 6.9-1Z"/>',
			'cart'       => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>',
			'search'     => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
			'user'       => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
			'calendar'   => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
			'home'       => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><path d="M9 22V12h6v10"/>',
			'menu'       => '<path d="M3 12h18M3 6h18M3 18h18"/>',
		);

		/**
		 * Filters the available icons.
		 *
		 * Values are SVG child elements, inserted into a 24 by 24 viewBox with
		 * a 2 unit stroke already applied. They are output with wp_kses, so
		 * only SVG shape elements and their geometry attributes survive.
		 *
		 * @param array $icons Icon markup keyed by name.
		 */
		return apply_filters( 'fbar_icons', $icons );
	}

	/**
	 * The names of every available icon.
	 *
	 * @return string[]
	 */
	public static function names() {
		return array_keys( self::all() );
	}

	/**
	 * One icon as a complete SVG element, already escaped.
	 *
	 * Decorative by definition: the accessible name comes from the item's
	 * label or its aria-label, so the glyph is hidden from assistive
	 * technology and removed from the tab order.
	 *
	 * @param string $name Icon name.
	 * @return string Empty string when the icon does not exist.
	 */
	public static function render( $name ) {
		$icons = self::all();

		if ( ! isset( $icons[ $name ] ) ) {
			return '';
		}

		return '<svg class="fbar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
			. wp_kses( $icons[ $name ], self::allowed_svg() )
			. '</svg>';
	}

	/**
	 * The SVG elements and attributes permitted inside an icon.
	 *
	 * The icon set is filterable, so this is what stops a filtered icon from
	 * carrying a script element or an event handler attribute.
	 *
	 * @return array
	 */
	private static function allowed_svg() {
		$geometry = array(
			'd'      => true,
			'cx'     => true,
			'cy'     => true,
			'r'      => true,
			'x'      => true,
			'y'      => true,
			'x1'     => true,
			'x2'     => true,
			'y1'     => true,
			'y2'     => true,
			'rx'     => true,
			'ry'     => true,
			'width'  => true,
			'height' => true,
			'points' => true,
			'fill'   => true,
		);

		return array(
			'path'     => $geometry,
			'circle'   => $geometry,
			'rect'     => $geometry,
			'line'     => $geometry,
			'polyline' => $geometry,
			'polygon'  => $geometry,
			'ellipse'  => $geometry,
			'g'        => array( 'fill' => true ),
		);
	}
}
