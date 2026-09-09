<?php
/**
 * The item type registry.
 *
 * A type declares what it is called, what icon it defaults to, what fields it
 * asks for, and how it becomes a hyperlink.
 *
 * @package FooterBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry of item types, their field shapes, and their link builders.
 */
class FBar_Item_Types {

	/**
	 * The kinds of value a type can ask for.
	 */
	const VALUE_KINDS = array( 'phone', 'email', 'url', 'text', 'selector', 'post', 'none' );

	/**
	 * Every registered type, keyed by slug.
	 *
	 * Passed through a filter, then validated, so a third party registering an
	 * incomplete type is dropped rather than causing a warning elsewhere.
	 *
	 * @return array
	 */
	public static function all() {
		/**
		 * Filters the registered item types.
		 *
		 * Each entry needs a label, an icon, an extra array, and a value array
		 * carrying a kind from FBar_Item_Types::VALUE_KINDS.
		 *
		 * @param array $types Registered types keyed by slug.
		 */
		$types = apply_filters( 'fbar_item_types', self::built_in() );

		if ( ! is_array( $types ) ) {
			return self::built_in();
		}

		return array_filter( $types, array( __CLASS__, 'is_valid_type' ) );
	}

	/**
	 * Whether a registry entry is usable.
	 *
	 * @param mixed $type Candidate entry.
	 * @return bool
	 */
	private static function is_valid_type( $type ) {
		if ( ! is_array( $type ) ) {
			return false;
		}

		foreach ( array( 'label', 'icon', 'value', 'extra' ) as $key ) {
			if ( ! array_key_exists( $key, $type ) ) {
				return false;
			}
		}

		if ( ! is_array( $type['value'] ) || ! isset( $type['value']['kind'] ) ) {
			return false;
		}

		return in_array( $type['value']['kind'], self::VALUE_KINDS, true );
	}

	/**
	 * The slugs of every registered type.
	 *
	 * @return string[]
	 */
	public static function slugs() {
		return array_keys( self::all() );
	}

	/**
	 * Whether a type is registered.
	 *
	 * @param mixed $slug Type slug.
	 * @return bool
	 */
	public static function exists( $slug ) {
		return is_string( $slug ) && array_key_exists( $slug, self::all() );
	}

	/**
	 * One type, or null.
	 *
	 * @param mixed $slug Type slug.
	 * @return array|null
	 */
	public static function get( $slug ) {
		$types = self::all();

		return isset( $types[ $slug ] ) ? $types[ $slug ] : null;
	}

	/**
	 * The kind of value a type asks for.
	 *
	 * @param mixed $slug Type slug.
	 * @return string One of VALUE_KINDS. Unknown types report none.
	 */
	public static function value_kind( $slug ) {
		$type = self::get( $slug );

		return $type ? $type['value']['kind'] : 'none';
	}

	/**
	 * The link target for an item, or an empty string when it has none.
	 *
	 * A type with no href renders as a button driven by script rather than a
	 * link, which is what "share" and "back to top" are.
	 *
	 * @param array $item Sanitised item.
	 * @return string
	 */
	public static function href( array $item ) {
		$value = isset( $item['value'] ) ? $item['value'] : '';
		$extra = isset( $item['extra'] ) && is_array( $item['extra'] ) ? $item['extra'] : array();

		switch ( $item['type'] ) {
			case 'call':
				return '' === $value ? '' : 'tel:' . $value;

			case 'sms':
				$body = isset( $extra['body'] ) && '' !== $extra['body']
					? '?body=' . rawurlencode( $extra['body'] )
					: '';
				return '' === $value ? '' : 'sms:' . $value . $body;

			case 'whatsapp':
				$digits = ltrim( $value, '+' );
				$text   = isset( $extra['text'] ) && '' !== $extra['text']
					? '?text=' . rawurlencode( $extra['text'] )
					: '';
				return '' === $digits ? '' : 'https://wa.me/' . $digits . $text;

			case 'email':
				$subject = isset( $extra['subject'] ) && '' !== $extra['subject']
					? '?subject=' . rawurlencode( $extra['subject'] )
					: '';
				return '' === $value ? '' : 'mailto:' . $value . $subject;

			case 'anchor':
				return '' === $value ? '' : ( 0 === strpos( $value, '#' ) ? $value : '#' . ltrim( $value, '#' ) );

			case 'page':
				// Resolved at render rather than stored, so a changed slug still works.
				$permalink = $value ? get_permalink( (int) $value ) : '';
				return $permalink ? $permalink : '';

			case 'directions':
				if ( '' === $value ) {
					return '';
				}
				return 0 === strpos( $value, 'http' )
					? $value
					: 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $value );

			case 'link':
			case 'social':
			case 'custom':
				return $value;

			case 'top':
			case 'share':
			case 'text':
			default:
				return '';
		}
	}

	/**
	 * The built-in types.
	 *
	 * @return array
	 */
	private static function built_in() {
		return array(
			'call'       => array(
				'label' => __( 'Call', 'footer-bar-mobile-action-bar' ),
				'icon'  => 'phone',
				'value' => array(
					'kind'  => 'phone',
					'label' => __( 'Phone number', 'footer-bar-mobile-action-bar' ),
				),
				'extra' => array(),
			),
			'whatsapp'   => array(
				'label' => __( 'WhatsApp', 'footer-bar-mobile-action-bar' ),
				'icon'  => 'message',
				'value' => array(
					'kind'  => 'phone',
					'label' => __( 'Number with country code', 'footer-bar-mobile-action-bar' ),
				),
				'extra' => array(
					'text' => array(
						'kind'  => 'text',
						'label' => __( 'Message to prefill', 'footer-bar-mobile-action-bar' ),
					),
				),
			),
			'sms'        => array(
				'label' => __( 'Text message', 'footer-bar-mobile-action-bar' ),
				'icon'  => 'message',
				'value' => array(
					'kind'  => 'phone',
					'label' => __( 'Phone number', 'footer-bar-mobile-action-bar' ),
				),
				'extra' => array(
					'body' => array(
						'kind'  => 'text',
						'label' => __( 'Message to prefill', 'footer-bar-mobile-action-bar' ),
					),
				),
			),
			'email'      => array(
				'label' => __( 'Email', 'footer-bar-mobile-action-bar' ),
				'icon'  => 'mail',
				'value' => array(
					'kind'  => 'email',
					'label' => __( 'Email address', 'footer-bar-mobile-action-bar' ),
				),
				'extra' => array(
					'subject' => array(
						'kind'  => 'text',
						'label' => __( 'Subject', 'footer-bar-mobile-action-bar' ),
					),
				),
			),
			'link'       => array(
				'label' => __( 'Link', 'footer-bar-mobile-action-bar' ),
				'icon'  => 'link',
				'value' => array(
					'kind'  => 'url',
					'label' => __( 'Address', 'footer-bar-mobile-action-bar' ),
				),
				'extra' => array(
					'new_tab'  => array(
						'kind'  => 'boolean',
						'label' => __( 'Open in a new tab', 'footer-bar-mobile-action-bar' ),
					),
					'nofollow' => array(
						'kind'  => 'boolean',
						'label' => __( 'Add nofollow', 'footer-bar-mobile-action-bar' ),
					),
				),
			),
			'social'     => array(
				'label' => __( 'Social profile', 'footer-bar-mobile-action-bar' ),
				'icon'  => 'share',
				'value' => array(
					'kind'  => 'url',
					'label' => __( 'Profile address', 'footer-bar-mobile-action-bar' ),
				),
				'extra' => array(
					'network' => array(
						'kind'    => 'choice',
						'label'   => __( 'Network', 'footer-bar-mobile-action-bar' ),
						'choices' => FBar_Icons::brand_names(),
					),
					'new_tab' => array(
						'kind'  => 'boolean',
						'label' => __( 'Open in a new tab', 'footer-bar-mobile-action-bar' ),
					),
				),
			),
			'anchor'     => array(
				'label' => __( 'Scroll to a section', 'footer-bar-mobile-action-bar' ),
				'icon'  => 'arrow-down',
				'value' => array(
					'kind'  => 'selector',
					'label' => __( 'Section id, such as #contact', 'footer-bar-mobile-action-bar' ),
				),
				'extra' => array(),
			),
			'page'       => array(
				'label' => __( 'Page', 'footer-bar-mobile-action-bar' ),
				'icon'  => 'file',
				'value' => array(
					'kind'  => 'post',
					'label' => __( 'Choose a page', 'footer-bar-mobile-action-bar' ),
				),
				'extra' => array(),
			),
			'directions' => array(
				'label' => __( 'Directions', 'footer-bar-mobile-action-bar' ),
				'icon'  => 'map-pin',
				'value' => array(
					'kind'  => 'text',
					'label' => __( 'Address or map link', 'footer-bar-mobile-action-bar' ),
				),
				'extra' => array(),
			),
			'share'      => array(
				'label' => __( 'Share', 'footer-bar-mobile-action-bar' ),
				'icon'  => 'share',
				'value' => array(
					'kind'  => 'none',
					'label' => '',
				),
				'extra' => array(),
			),
			'top'        => array(
				'label' => __( 'Back to top', 'footer-bar-mobile-action-bar' ),
				'icon'  => 'arrow-up',
				'value' => array(
					'kind'  => 'none',
					'label' => '',
				),
				'extra' => array(),
			),
			'custom'     => array(
				'label' => __( 'Anything else', 'footer-bar-mobile-action-bar' ),
				'icon'  => 'star',
				'value' => array(
					'kind'  => 'url',
					'label' => __( 'Address', 'footer-bar-mobile-action-bar' ),
				),
				'extra' => array(),
			),
		);
	}
}
