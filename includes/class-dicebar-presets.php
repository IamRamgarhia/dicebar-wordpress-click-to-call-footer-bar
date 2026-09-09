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
 * @package DiceBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ready-made item sets, grouped by how many buttons they use.
 */
class DiceBar_Presets {

	/**
	 * Every starter kit.
	 *
	 * @return array
	 */
	public static function all() {
		$kits = array(
			array(
				'id'    => 'call-whatsapp',
				'name'  => __( 'Call and WhatsApp', 'dicebar' ),
				'note'  => __( 'The two buttons most small businesses actually need.', 'dicebar' ),
				'items' => array(
					array(
						'type'    => 'call',
						'label'   => __( 'Call', 'dicebar' ),
						'primary' => true,
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'dicebar' ),
					),
				),
			),
			array(
				'id'    => 'call-message-directions',
				'name'  => __( 'Call, message, directions', 'dicebar' ),
				'note'  => __( 'For a shop or office people visit in person.', 'dicebar' ),
				'items' => array(
					array(
						'type'    => 'call',
						'label'   => __( 'Call', 'dicebar' ),
						'primary' => true,
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'dicebar' ),
					),
					array(
						'type'  => 'directions',
						'label' => __( 'Find us', 'dicebar' ),
					),
				),
			),
			array(
				'id'    => 'restaurant',
				'name'  => __( 'Restaurant', 'dicebar' ),
				'note'  => __( 'Book a table, ask a question, find the door, read the menu.', 'dicebar' ),
				'items' => array(
					array(
						'type'    => 'call',
						'label'   => __( 'Book', 'dicebar' ),
						'primary' => true,
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'dicebar' ),
					),
					array(
						'type'  => 'directions',
						'label' => __( 'Find us', 'dicebar' ),
					),
					array(
						'type'  => 'link',
						'label' => __( 'Menu', 'dicebar' ),
						'icon'  => 'file',
					),
				),
			),
			array(
				'id'    => 'clinic',
				'name'  => __( 'Clinic or salon', 'dicebar' ),
				'note'  => __( 'Booking is the standout button; everything else supports it.', 'dicebar' ),
				'items' => array(
					array(
						'type'    => 'link',
						'label'   => __( 'Book', 'dicebar' ),
						'icon'    => 'calendar',
						'primary' => true,
					),
					array(
						'type'  => 'call',
						'label' => __( 'Call', 'dicebar' ),
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'dicebar' ),
					),
					array(
						'type'  => 'directions',
						'label' => __( 'Find us', 'dicebar' ),
					),
				),
			),
			array(
				'id'    => 'trade',
				'name'  => __( 'Trade or service call-out', 'dicebar' ),
				'note'  => __( 'Someone with a leak is not filling in a contact form.', 'dicebar' ),
				'items' => array(
					array(
						'type'    => 'call',
						'label'   => __( 'Call now', 'dicebar' ),
						'primary' => true,
					),
					array(
						'type'  => 'whatsapp',
						'label' => __( 'WhatsApp', 'dicebar' ),
					),
					array(
						'type'  => 'link',
						'label' => __( 'Quote', 'dicebar' ),
						'icon'  => 'file',
					),
				),
			),
			array(
				'id'    => 'shop',
				'name'  => __( 'Online shop', 'dicebar' ),
				'note'  => __( 'The four places a shopper moves between on a phone.', 'dicebar' ),
				'items' => array(
					array(
						'type'  => 'link',
						'label' => __( 'Shop', 'dicebar' ),
						'icon'  => 'home',
					),
					array(
						'type'  => 'link',
						'label' => __( 'Search', 'dicebar' ),
						'icon'  => 'search',
					),
					array(
						'type'    => 'link',
						'label'   => __( 'Cart', 'dicebar' ),
						'icon'    => 'cart',
						'primary' => true,
					),
					array(
						'type'  => 'link',
						'label' => __( 'Account', 'dicebar' ),
						'icon'  => 'user',
					),
				),
			),
			array(
				'id'    => 'social',
				'name'  => __( 'Social profiles', 'dicebar' ),
				'note'  => __( 'Icon-only suits this one. Turn labels off on the Design tab.', 'dicebar' ),
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
				'name'  => __( 'Blog or magazine', 'dicebar' ),
				'note'  => __( 'Sharing and getting back up, which is all a reader wants.', 'dicebar' ),
				'items' => array(
					array(
						'type'  => 'share',
						'label' => __( 'Share', 'dicebar' ),
					),
					array(
						'type'    => 'link',
						'label'   => __( 'Subscribe', 'dicebar' ),
						'icon'    => 'mail',
						'primary' => true,
					),
					array(
						'type'  => 'top',
						'label' => __( 'Top', 'dicebar' ),
					),
				),
			),
			array(
				'id'    => 'portfolio',
				'name'  => __( 'Portfolio', 'dicebar' ),
				'note'  => __( 'One way to reach you, one way to see the work.', 'dicebar' ),
				'items' => array(
					array(
						'type'    => 'email',
						'label'   => __( 'Email', 'dicebar' ),
						'primary' => true,
					),
					array(
						'type'  => 'link',
						'label' => __( 'Work', 'dicebar' ),
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
		return apply_filters( 'dicebar_presets', $kits );
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

			$items = DiceBar_Sanitize::items( $kit['items'] );

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
