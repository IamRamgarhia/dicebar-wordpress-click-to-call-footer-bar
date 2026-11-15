<?php
/**
 * Tests for item ordering, the item cap, and when labels fit.
 *
 * @package DiceBar
 */

/**
 * Covers the two decisions 1.10.0 added: what order the items end up in, and
 * whether there is room to show their labels.
 */
class ItemOrderTest extends WP_UnitTestCase {

	/**
	 * Build a list of items, optionally with positions.
	 *
	 * @param array $spec One entry per item: type => position, or just type.
	 * @return array
	 */
	private function items( array $spec ) {
		$out = array();
		$n   = 0;

		foreach ( $spec as $key => $value ) {
			++$n;

			$type     = is_int( $key ) ? $value : $key;
			$position = is_int( $key ) ? null : $value;

			$item = array(
				'id'    => 'itm_' . $n,
				'type'  => $type,
				'value' => '+1555000000' . $n,
				'label' => 'Item ' . $n,
			);

			if ( null !== $position ) {
				$item['position'] = $position;
			}

			$out[] = $item;
		}

		return $out;
	}

	/**
	 * The types that came out, in the order they came out.
	 *
	 * @param array $items Items as sanitised.
	 * @return string
	 */
	private function order( array $items ) {
		return implode( ',', wp_list_pluck( $items, 'type' ) );
	}

	// ------------------------------------------------------------- order.

	/**
	 * The position field decides the order, not the order of arrival.
	 *
	 * This is what makes the numbered select work with JavaScript off, on a
	 * touch screen with nothing to drag, and from the keyboard.
	 */
	public function test_items_come_back_in_the_order_their_positions_ask_for() {
		$items = DiceBar_Sanitize::items(
			$this->items(
				array(
					'call'     => '3',
					'whatsapp' => '1',
					'email'    => '2',
				)
			)
		);

		$this->assertSame( 'whatsapp,email,call', $this->order( $items ) );
	}

	/**
	 * With no positions at all, arrival order is kept.
	 *
	 * Settings saved by an earlier version carry no position field, so this
	 * is what stops an upgrade shuffling somebody's bar.
	 */
	public function test_items_without_positions_keep_the_order_they_arrived_in() {
		$items = DiceBar_Sanitize::items( $this->items( array( 'call', 'whatsapp', 'email' ) ) );

		$this->assertSame( 'call,whatsapp,email', $this->order( $items ) );
	}

	/**
	 * Two items claiming one slot must not swap places on every save.
	 *
	 * PHP's usort() is not stable, so without an explicit tie-break on the
	 * arrival index the order here would be undefined and the bar would
	 * reshuffle itself each time the form was submitted.
	 */
	public function test_two_items_in_the_same_slot_do_not_swap_on_every_save() {
		$spec = $this->items(
			array(
				'call'     => '1',
				'whatsapp' => '1',
			)
		);

		$first  = $this->order( DiceBar_Sanitize::items( $spec ) );
		$second = $this->order( DiceBar_Sanitize::items( $spec ) );

		$this->assertSame( 'call,whatsapp', $first );
		$this->assertSame( $first, $second );
	}

	/**
	 * A position that is not a number must not lose the item.
	 */
	public function test_an_unusable_position_keeps_the_item() {
		$items = DiceBar_Sanitize::items(
			array(
				array(
					'id'       => 'itm_1',
					'type'     => 'call',
					'value'    => '+15550000001',
					'position' => 'not a number',
				),
			)
		);

		$this->assertCount( 1, $items );
		$this->assertSame( 'call', $items[0]['type'] );
	}

	/**
	 * The cap holds, and it is the constant rather than a literal.
	 */
	public function test_no_more_items_than_the_cap_survive() {
		$spec = array();

		for ( $i = 1; $i <= DiceBar_Sanitize::MAX_ITEMS + 4; $i++ ) {
			$spec[] = 'call';
		}

		$this->assertCount(
			DiceBar_Sanitize::MAX_ITEMS,
			DiceBar_Sanitize::items( $this->items( $spec ) )
		);
	}

	// ------------------------------------------------------------ labels.

	/**
	 * Render a bar with a given number of items.
	 *
	 * @param int    $count How many items.
	 * @param string $mode  Label mode.
	 * @return string Markup.
	 */
	private function bar( $count, $mode = 'icon_label' ) {
		$spec = array();

		for ( $i = 1; $i <= $count; $i++ ) {
			$spec[] = 'call';
		}

		$settings = DiceBar_Sanitize::settings(
			array(
				'enabled' => '1',
				'items'   => $this->items( $spec ),
				'style'   => array( 'label' => array( 'mode' => $mode ) ),
			)
		);

		return DiceBar_Render::bar( $settings, 'inline' );
	}

	/**
	 * At or under the limit, labels are shown and carry the name themselves.
	 */
	public function test_labels_show_while_there_is_room_for_them() {
		$markup = $this->bar( DiceBar_Sanitize::LABELS_FIT );

		$this->assertSame(
			DiceBar_Sanitize::LABELS_FIT,
			substr_count( $markup, 'dicebar__label' )
		);
		$this->assertStringNotContainsString( 'aria-label="Item', $markup );
	}

	/**
	 * Past the limit the labels go, and an aria-label takes over.
	 *
	 * The failure this prevents: hiding the labels in the stylesheet instead
	 * would take them out of the accessibility tree as well as off the
	 * screen, leaving every item in a five-icon bar with no accessible name
	 * at all. The markup has to know, so that the aria-label is added.
	 */
	public function test_past_the_limit_every_item_still_has_a_name() {
		$count  = DiceBar_Sanitize::LABELS_FIT + 1;
		$markup = $this->bar( $count );

		$this->assertSame( 0, substr_count( $markup, 'dicebar__label' ), 'a label survived past the limit' );
		$this->assertSame( $count, substr_count( $markup, 'aria-label="Item' ), 'an item lost its accessible name' );
		$this->assertSame( $count, substr_count( $markup, 'dicebar__icon' ) );
	}

	/**
	 * A bar set to labels only still shows something past the limit.
	 *
	 * Dropping the labels from a labels-only bar without putting the icons
	 * back would render a row of empty buttons.
	 */
	public function test_a_labels_only_bar_falls_back_to_icons_rather_than_to_nothing() {
		$count  = DiceBar_Sanitize::LABELS_FIT + 2;
		$markup = $this->bar( $count, 'label' );

		$this->assertSame( 0, substr_count( $markup, 'dicebar__label' ) );
		$this->assertSame( $count, substr_count( $markup, 'dicebar__icon' ), 'the bar renders buttons with nothing in them' );
		$this->assertSame( $count, substr_count( $markup, 'aria-label="Item' ) );
	}
}
