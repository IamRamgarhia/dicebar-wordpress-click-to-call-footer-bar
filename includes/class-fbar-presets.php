<?php
/**
 * Starter kits.
 *
 * A kit fills the item list in one click. They exist because the hardest part
 * of a bar like this is not configuring it, it is deciding what belongs in it,
 * and an empty screen gives no help with that.
 *
 * Every kit is editable the moment it is applied. Nothing here is a mode the
 * owner is then stuck inside.
 *
 * @package FooterBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ready-made item sets, grouped by how many buttons they use.
 */
class FBar_Presets {

	/**
	 * Every starter kit.
	 *
	 * @return array
	 */
	public static function all() {
		$kits = array(
			array(
				'id'    => 'call-whatsapp',
				'name'  => __( 'Call and WhatsApp', 'footer-bar-mobile-action-bar' ),
				'note'  => __( 'The two buttons most small businesses actually need.', 'footer-bar-mobile-action-bar' ),
				'items' => array(
					array(
						'type'    => 'call',
						'label'   => __( 'Call', 'footer-bar-mobile-action-bar' ),
						'primary' => true,
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'footer-bar-mobile-action-bar' ),
					),
				),
			),
			array(
				'id'    => 'call-message-directions',
				'name'  => __( 'Call, message, directions', 'footer-bar-mobile-action-bar' ),
				'note'  => __( 'For a shop or office people visit in person.', 'footer-bar-mobile-action-bar' ),
				'items' => array(
					array(
						'type'    => 'call',
						'label'   => __( 'Call', 'footer-bar-mobile-action-bar' ),
						'primary' => true,
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'footer-bar-mobile-action-bar' ),
					),
					array(
						'type'  => 'directions',
						'label' => __( 'Find us', 'footer-bar-mobile-action-bar' ),
					),
				),
			),
			array(
				'id'    => 'restaurant',
				'name'  => __( 'Restaurant', 'footer-bar-mobile-action-bar' ),
				'note'  => __( 'Book a table, ask a question, find the door, read the menu.', 'footer-bar-mobile-action-bar' ),
				'items' => array(
					array(
						'type'    => 'call',
						'label'   => __( 'Book', 'footer-bar-mobile-action-bar' ),
						'primary' => true,
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'footer-bar-mobile-action-bar' ),
					),
					array(
						'type'  => 'directions',
						'label' => __( 'Find us', 'footer-bar-mobile-action-bar' ),
					),
					array(
						'type'  => 'link',
						'label' => __( 'Menu', 'footer-bar-mobile-action-bar' ),
						'icon'  => 'file',
					),
				),
			),
			array(
				'id'    => 'clinic',
				'name'  => __( 'Clinic or salon', 'footer-bar-mobile-action-bar' ),
				'note'  => __( 'Booking is the standout button; everything else supports it.', 'footer-bar-mobile-action-bar' ),
				'items' => array(
					array(
						'type'    => 'link',
						'label'   => __( 'Book', 'footer-bar-mobile-action-bar' ),
						'icon'    => 'calendar',
						'primary' => true,
					),
					array(
						'type'  => 'call',
						'label' => __( 'Call', 'footer-bar-mobile-action-bar' ),
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'footer-bar-mobile-action-bar' ),
					),
					array(
						'type'  => 'directions',
						'label' => __( 'Find us', 'footer-bar-mobile-action-bar' ),
					),
				),
			),
			array(
				'id'    => 'trade',
				'name'  => __( 'Trade or service call-out', 'footer-bar-mobile-action-bar' ),
				'note'  => __( 'Someone with a leak is not filling in a contact form.', 'footer-bar-mobile-action-bar' ),
				'items' => array(
					array(
						'type'    => 'call',
						'label'   => __( 'Call now', 'footer-bar-mobile-action-bar' ),
						'primary' => true,
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'footer-bar-mobile-action-bar' ),
					),
					array(
						'type'  => 'link',
						'label' => __( 'Quote', 'footer-bar-mobile-action-bar' ),
						'icon'  => 'file',
					),
				),
			),
			array(
				'id'    => 'shop',
				'name'  => __( 'Online shop', 'footer-bar-mobile-action-bar' ),
				'note'  => __( 'The four places a shopper moves between on a phone.', 'footer-bar-mobile-action-bar' ),
				'items' => array(
					array(
						'type'  => 'link',
						'label' => __( 'Shop', 'footer-bar-mobile-action-bar' ),
						'icon'  => 'home',
					),
					array(
						'type'  => 'link',
						'label' => __( 'Search', 'footer-bar-mobile-action-bar' ),
						'icon'  => 'search',
					),
					array(
						'type'    => 'link',
						'label'   => __( 'Cart', 'footer-bar-mobile-action-bar' ),
						'icon'    => 'cart',
						'primary' => true,
					),
					array(
						'type'  => 'link',
						'label' => __( 'Account', 'footer-bar-mobile-action-bar' ),
						'icon'  => 'user',
					),
				),
			),
			array(
				'id'    => 'social',
				'name'  => __( 'Social profiles', 'footer-bar-mobile-action-bar' ),
				'note'  => __( 'Icon-only suits this one. Turn labels off on the Design tab.', 'footer-bar-mobile-action-bar' ),
				'items' => array(
					array(
						'type'  => 'social',
						'label' => 'Instagram',
						'extra' => array(
							'network' => 'instagram',
							'new_tab' => true,
						),
					),
					array(
						'type'  => 'social',
						'label' => 'Facebook',
						'extra' => array(
							'network' => 'facebook',
							'new_tab' => true,
						),
					),
					array(
						'type'  => 'social',
						'label' => 'YouTube',
						'extra' => array(
							'network' => 'youtube',
							'new_tab' => true,
						),
					),
					array(
						'type'  => 'whatsapp',
						'label' => 'WhatsApp',
					),
				),
			),
			array(
				'id'    => 'blog',
				'name'  => __( 'Blog or magazine', 'footer-bar-mobile-action-bar' ),
				'note'  => __( 'Sharing and getting back up, which is all a reader wants.', 'footer-bar-mobile-action-bar' ),
				'items' => array(
					array(
						'type'  => 'share',
						'label' => __( 'Share', 'footer-bar-mobile-action-bar' ),
					),
					array(
						'type'    => 'link',
						'label'   => __( 'Subscribe', 'footer-bar-mobile-action-bar' ),
						'icon'    => 'mail',
						'primary' => true,
					),
					array(
						'type'  => 'top',
						'label' => __( 'Top', 'footer-bar-mobile-action-bar' ),
					),
				),
			),
			array(
				'id'    => 'portfolio',
				'name'  => __( 'Portfolio', 'footer-bar-mobile-action-bar' ),
				'note'  => __( 'One way to reach you, one way to see the work.', 'footer-bar-mobile-action-bar' ),
				'items' => array(
					array(
						'type'    => 'email',
						'label'   => __( 'Email', 'footer-bar-mobile-action-bar' ),
						'primary' => true,
					),
					array(
						'type'  => 'link',
						'label' => __( 'Work', 'footer-bar-mobile-action-bar' ),
						'icon'  => 'star',
					),
					array(
						'type'  => 'social',
						'label' => 'Instagram',
						'extra' => array(
							'network' => 'instagram',
							'new_tab' => true,
						),
					),
				),
			),
		);

		/**
		 * Filters the starter kits offered on the settings screen.
		 *
		 * @param array $kits Kits, each with an id, name, note and items.
		 */
		return apply_filters( 'fbar_presets', $kits );
	}

	/**
	 * The kits in the shape the admin script needs, with items sanitised.
	 *
	 * Passing them through the sanitiser here means the script never has to
	 * know which fields a type declares, and a kit added by a filter cannot
	 * introduce an item the rest of the plugin would reject.
	 *
	 * @return array
	 */
	public static function for_script() {
		$out = array();

		foreach ( self::all() as $kit ) {
			if ( ! is_array( $kit ) || empty( $kit['items'] ) ) {
				continue;
			}

			$items = FBar_Sanitize::items( $kit['items'] );

			if ( ! $items ) {
				continue;
			}

			$out[] = array(
				'id'    => isset( $kit['id'] ) ? sanitize_key( $kit['id'] ) : '',
				'name'  => isset( $kit['name'] ) ? $kit['name'] : '',
				'note'  => isset( $kit['note'] ) ? $kit['note'] : '',
				'count' => count( $items ),
				'items' => $items,
			);
		}

		return $out;
	}
}
