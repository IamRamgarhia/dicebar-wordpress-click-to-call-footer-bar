<?php
/**
 * Tests for per-screen item visibility and the label arithmetic that follows.
 *
 * @package DiceBar
 */

/**
 * Covers the thing 1.11.0 added: an item can show on some screens and not
 * others, so "three buttons on a phone and six on a desktop" is one bar.
 */
class PerScreenTest extends WP_UnitTestCase {

	/**
	 * Settings for a bar whose items carry the given device rules.
	 *
	 * @param array  $rules One device rule per item.
	 * @param string $bar   The bar's own device rule.
	 * @return array Sanitised settings.
	 */
	private function settings( array $rules, $bar = 'all' ) {
		$items = array();
		$n     = 0;

		foreach ( $rules as $rule ) {
			++$n;

			$items[] = array(
				'id'    => 'itm_' . $n,
				'type'  => 'call',
				'value' => '+1555000000' . $n,
				'label' => 'Item ' . $n,
				'show'  => array( 'devices' => $rule ),
			);
		}

		return DiceBar_Sanitize::settings(
			array(
				'enabled' => '1',
				'items'   => $items,
				'display' => array( 'devices' => $bar ),
			)
		);
	}

	// ------------------------------------------------------- the vocabulary.

	/**
	 * Every rule shows where it says and nowhere else.
	 *
	 * Written out rather than looped so a wrong answer names itself.
	 */
	public function test_each_device_rule_shows_on_the_screens_it_names() {
		$expected = array(
			'phone'        => array( 'phone' ),
			'phone_tablet' => array( 'phone', 'tablet' ),
			'tablet'       => array( 'tablet' ),
			'tablet_up'    => array( 'tablet', 'desktop' ),
			'desktop'      => array( 'desktop' ),
			'all'          => array( 'phone', 'tablet', 'desktop' ),
		);

		foreach ( $expected as $rule => $screens ) {
			foreach ( DiceBar_Sanitize::SCREENS as $screen ) {
				$this->assertSame(
					in_array( $screen, $screens, true ),
					DiceBar_Sanitize::shows_on( $rule, $screen ),
					$rule . ' on ' . $screen
				);
			}
		}
	}

	/**
	 * The three new rules survive being saved.
	 *
	 * A value missing from the allowlist falls back to the default, so this
	 * is what proves the new options are actually storable rather than
	 * silently reset on the first save.
	 */
	public function test_the_new_rules_survive_a_save() {
		foreach ( array( 'tablet', 'tablet_up', 'desktop' ) as $rule ) {
			$settings = $this->settings( array( $rule ) );

			$this->assertSame( $rule, $settings['items'][0]['show']['devices'], $rule . ' was not kept' );
		}
	}

	// ----------------------------------------------------------- the counts.

	/**
	 * Three on a phone, four on a tablet, six on a desktop.
	 *
	 * The arrangement this feature exists for, asserted as a whole rather
	 * than one rule at a time.
	 */
	public function test_a_bar_can_carry_more_items_as_the_screen_widens() {
		$settings = $this->settings(
			array( 'inherit', 'inherit', 'inherit', 'tablet_up', 'desktop', 'desktop' )
		);

		$this->assertSame(
			array(
				'phone'   => 3,
				'tablet'  => 4,
				'desktop' => 6,
			),
			DiceBar_Render::items_per_screen( $settings )
		);
	}

	/**
	 * An item cannot show where the bar itself does not.
	 *
	 * Otherwise "phones only" on the bar would be undone by one item asking
	 * for desktops, and the setting above would not mean what it says.
	 */
	public function test_an_item_cannot_escape_the_bars_own_rule() {
		$settings = $this->settings( array( 'inherit', 'desktop' ), 'phone' );

		$counts = DiceBar_Render::items_per_screen( $settings );

		$this->assertSame( 1, $counts['phone'] );
		$this->assertSame( 0, $counts['tablet'] );
		$this->assertSame( 0, $counts['desktop'], 'a desktop-only item showed on a phones-only bar' );
	}

	// ----------------------------------------------------------- the labels.

	/**
	 * Labels are judged per screen, not on the total.
	 *
	 * The failure this prevents: counting all six items would drop the
	 * labels on the phone too, where only three of them show and there is
	 * room for every one.
	 */
	public function test_labels_are_judged_per_screen_and_not_on_the_total() {
		$settings = $this->settings(
			array( 'inherit', 'inherit', 'inherit', 'tablet_up', 'desktop', 'desktop' )
		);

		$this->assertSame(
			array(
				'phone'   => true,
				'tablet'  => true,
				'desktop' => false,
			),
			DiceBar_Render::labels_fit_per_screen( $settings )
		);
	}

	/**
	 * Where labels fit on some screens and not others, both names are there.
	 *
	 * The label for the screens that show it, and an aria-label for the ones
	 * where the stylesheet hides it -- display:none takes the label out of
	 * the accessibility tree, so without the aria-label those items would
	 * have no accessible name at exactly those widths.
	 */
	public function test_a_label_hidden_only_at_some_widths_still_leaves_a_name() {
		$settings = $this->settings(
			array( 'inherit', 'inherit', 'inherit', 'tablet_up', 'desktop', 'desktop' )
		);

		$markup = DiceBar_Render::bar( $settings, 'inline' );

		$this->assertSame( 6, substr_count( $markup, 'dicebar__label' ), 'the labels are not in the markup' );
		$this->assertSame( 6, substr_count( $markup, 'aria-label="Item' ), 'an item has no name where its label is hidden' );
	}

	/**
	 * A bar whose labels fit everywhere carries no aria-label.
	 *
	 * Two sources for one name is a smell even where it is harmless, so it
	 * is only done where it is the only way to keep a name at all.
	 */
	public function test_a_bar_that_fits_its_labels_everywhere_adds_no_aria_label() {
		$settings = $this->settings( array( 'inherit', 'inherit' ) );

		$markup = DiceBar_Render::bar( $settings, 'inline' );

		$this->assertSame( 2, substr_count( $markup, 'dicebar__label' ) );
		$this->assertStringNotContainsString( 'aria-label="Item', $markup );
	}

	// ----------------------------------------------------- the stylesheet.

	/**
	 * The label rule names a descendant, not one element with two classes.
	 *
	 * `.dicebar.dicebar__label` matches nothing, and a stylesheet that
	 * matches nothing fails silently: the labels it should have hidden stay
	 * on screen and clip, which is the failure the per-screen arithmetic
	 * exists to prevent. This was written that way once.
	 */
	public function test_the_label_rule_is_a_descendant_selector() {
		$settings = $this->settings(
			array( 'inherit', 'inherit', 'inherit', 'tablet_up', 'desktop', 'desktop' )
		);

		update_option( DiceBar_Settings::OPTION, $settings );
		DiceBar_Settings::flush();

		$css = DiceBar_Styles::css();

		$this->assertStringContainsString( '.dicebar .dicebar__label{display:none}', $css );
		$this->assertStringNotContainsString( '.dicebar.dicebar__label', $css );
	}

	/**
	 * Each new rule gets a query, at the edge it belongs to.
	 */
	public function test_the_stylesheet_carries_a_query_for_every_new_rule() {
		update_option( DiceBar_Settings::OPTION, $this->settings( array( 'inherit' ) ) );
		DiceBar_Settings::flush();

		$css = DiceBar_Styles::css();

		// Below a tablet, none of the three widening rules show.
		$this->assertStringContainsString( '@media (max-width:767px){', $css );
		$this->assertStringContainsString( '.dicebar__item--device-tablet_up{display:none}', $css );

		// Below a desktop, the desktop-only ones do not.
		$this->assertStringContainsString( '@media (max-width:1023px){', $css );
		$this->assertStringContainsString( '.dicebar__item--device-desktop{display:none}', $css );
	}
}
