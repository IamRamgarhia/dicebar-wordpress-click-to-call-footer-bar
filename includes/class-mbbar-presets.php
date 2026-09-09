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
 * @package MobileBottomBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ready-made item sets, grouped by how many buttons they use.
 */
class MBBar_Presets {

	/**
	 * Every starter kit.
	 *
	 * @return array
	 */
	public static function all() {
		$kits = array(
			array(
				'id'    => 'call-whatsapp',
				'name'  => __( 'Call and WhatsApp', 'mobile-bottom-bar' ),
				'note'  => __( 'The two buttons most small businesses actually need.', 'mobile-bottom-bar' ),
				'items' => array(
					array(
						'type'    => 'call',
						'label'   => __( 'Call', 'mobile-bottom-bar' ),
						'primary' => true,
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'mobile-bottom-bar' ),
					),
				),
			),
			array(
				'id'    => 'call-message-directions',
				'name'  => __( 'Call, message, directions', 'mobile-bottom-bar' ),
				'note'  => __( 'For a shop or office people visit in person.', 'mobile-bottom-bar' ),
				'items' => array(
					array(
						'type'    => 'call',
						'label'   => __( 'Call', 'mobile-bottom-bar' ),
						'primary' => true,
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'mobile-bottom-bar' ),
					),
					array(
						'type'  => 'directions',
						'label' => __( 'Find us', 'mobile-bottom-bar' ),
					),
				),
			),
			array(
				'id'    => 'restaurant',
				'name'  => __( 'Restaurant', 'mobile-bottom-bar' ),
				'note'  => __( 'Book a table, ask a question, find the door, read the menu.', 'mobile-bottom-bar' ),
				'items' => array(
					array(
						'type'    => 'call',
						'label'   => __( 'Book', 'mobile-bottom-bar' ),
						'primary' => true,
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'mobile-bottom-bar' ),
					),
					array(
						'type'  => 'directions',
						'label' => __( 'Find us', 'mobile-bottom-bar' ),
					),
					array(
						'type'  => 'link',
						'label' => __( 'Menu', 'mobile-bottom-bar' ),
						'icon'  => 'file',
					),
				),
			),
			array(
				'id'    => 'clinic',
				'name'  => __( 'Clinic or salon', 'mobile-bottom-bar' ),
				'note'  => __( 'Booking is the standout button; everything else supports it.', 'mobile-bottom-bar' ),
				'items' => array(
					array(
						'type'    => 'link',
						'label'   => __( 'Book', 'mobile-bottom-bar' ),
						'icon'    => 'calendar',
						'primary' => true,
					),
					array(
						'type'  => 'call',
						'label' => __( 'Call', 'mobile-bottom-bar' ),
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'mobile-bottom-bar' ),
					),
					array(
						'type'  => 'directions',
						'label' => __( 'Find us', 'mobile-bottom-bar' ),
					),
				),
			),
			array(
				'id'    => 'trade',
				'name'  => __( 'Trade or service call-out', 'mobile-bottom-bar' ),
				'note'  => __( 'Someone with a leak is not filling in a contact form.', 'mobile-bottom-bar' ),
				'items' => array(
					array(
						'type'    => 'call',
						'label'   => __( 'Call now', 'mobile-bottom-bar' ),
						'primary' => true,
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'mobile-bottom-bar' ),
					),
					array(
						'type'  => 'link',
						'label' => __( 'Quote', 'mobile-bottom-bar' ),
						'icon'  => 'file',
					),
				),
			),
			array(
				'id'    => 'shop',
				'name'  => __( 'Online shop', 'mobile-bottom-bar' ),
				'note'  => __( 'The four places a shopper moves between on a phone.', 'mobile-bottom-bar' ),
				'items' => array(
					array(
						'type'  => 'link',
						'label' => __( 'Shop', 'mobile-bottom-bar' ),
						'icon'  => 'home',
					),
					array(
						'type'  => 'link',
						'label' => __( 'Search', 'mobile-bottom-bar' ),
						'icon'  => 'search',
					),
					array(
						'type'    => 'link',
						'label'   => __( 'Cart', 'mobile-bottom-bar' ),
						'icon'    => 'cart',
						'primary' => true,
					),
					array(
						'type'  => 'link',
						'label' => __( 'Account', 'mobile-bottom-bar' ),
						'icon'  => 'user',
					),
				),
			),
			array(
				'id'    => 'social',
				'name'  => __( 'Social profiles', 'mobile-bottom-bar' ),
				'note'  => __( 'Icon-only suits this one. Turn labels off on the Design tab.', 'mobile-bottom-bar' ),
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
				'name'  => __( 'Blog or magazine', 'mobile-bottom-bar' ),
				'note'  => __( 'Sharing and getting back up, which is all a reader wants.', 'mobile-bottom-bar' ),
				'items' => array(
					array(
						'type'  => 'share',
						'label' => __( 'Share', 'mobile-bottom-bar' ),
					),
					array(
						'type'    => 'link',
						'label'   => __( 'Subscribe', 'mobile-bottom-bar' ),
						'icon'    => 'mail',
						'primary' => true,
					),
					array(
						'type'  => 'top',
						'label' => __( 'Top', 'mobile-bottom-bar' ),
					),
				),
			),
			array(
				'id'    => 'portfolio',
				'name'  => __( 'Portfolio', 'mobile-bottom-bar' ),
				'note'  => __( 'One way to reach you, one way to see the work.', 'mobile-bottom-bar' ),
				'items' => array(
					array(
						'type'    => 'email',
						'label'   => __( 'Email', 'mobile-bottom-bar' ),
						'primary' => true,
					),
					array(
						'type'  => 'link',
						'label' => __( 'Work', 'mobile-bottom-bar' ),
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
		return apply_filters( 'mbbar_presets', $kits );
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

			$items = MBBar_Sanitize::items( $kit['items'] );

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
