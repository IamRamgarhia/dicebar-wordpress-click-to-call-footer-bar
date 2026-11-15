<?php
/**
 * Tests for the review request.
 *
 * @package DiceBar
 */

/**
 * The promise this makes is "at most twice, only on our own screens, and no
 * means no". Every one of those is asserted here, because the failure mode is
 * nagging somebody on their own dashboard and never hearing about it.
 */
class ReviewTest extends WP_UnitTestCase {

	/**
	 * An administrator, so the capability check passes.
	 *
	 * @var int
	 */
	private $admin = 0;

	/**
	 * Sign in as an administrator and start from a clean state.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// The plugin only loads its admin classes behind is_admin(), which is
		// false under the test suite, so they are pulled in here rather than
		// loaded on the front end for everybody to pay for.
		require_once DICEBAR_PATH . 'admin/class-dicebar-admin.php';
		require_once DICEBAR_PATH . 'admin/class-dicebar-review.php';

		$this->admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->admin );

		delete_option( DiceBar_Review::OPTION );
		delete_option( DiceBar_Settings::OPTION );
		DiceBar_Settings::flush();
	}

	/**
	 * Leave nothing behind for the next test.
	 *
	 * @return void
	 */
	public function tear_down() {
		delete_option( DiceBar_Review::OPTION );
		delete_option( DiceBar_Settings::OPTION );
		wp_set_current_user( 0 );

		parent::tear_down();
	}

	/**
	 * Put a configured bar in place.
	 *
	 * @return void
	 */
	private function configure() {
		update_option(
			DiceBar_Settings::OPTION,
			DiceBar_Sanitize::settings(
				array(
					'enabled' => '1',
					'items'   => array(
						array(
							'id'    => 'itm_1',
							'type'  => 'call',
							'value' => '+15550000001',
						),
					),
				)
			)
		);

		DiceBar_Settings::flush();
	}

	/**
	 * Pretend we are on a DiceBar screen.
	 *
	 * @return void
	 */
	private function on_our_screen() {
		set_current_screen( 'toplevel_page_' . DiceBar_Admin::SLUG );
	}

	/**
	 * What the notice would print right now.
	 *
	 * @return string
	 */
	private function notice() {
		ob_start();
		DiceBar_Review::maybe_ask();

		return (string) ob_get_clean();
	}

	/**
	 * The stored state.
	 *
	 * @return array
	 */
	private function state() {
		return (array) get_option( DiceBar_Review::OPTION, array() );
	}

	// ------------------------------------------------------- when it asks.

	/**
	 * Installing the plugin starts no clock.
	 *
	 * The commonest complaint about review prompts is being asked on
	 * activation, before the plugin has done anything.
	 */
	public function test_an_unconfigured_plugin_never_asks() {
		DiceBar_Review::note_configured();
		$this->on_our_screen();

		$this->assertSame( '', $this->notice() );
		$this->assertSame( array(), $this->state() );
	}

	/**
	 * Configuring it starts the clock but does not ask.
	 */
	public function test_configuring_it_starts_the_clock_without_asking() {
		$this->configure();
		DiceBar_Review::note_configured();
		$this->on_our_screen();

		$state = $this->state();

		$this->assertGreaterThan( 0, $state['ready'] );
		$this->assertGreaterThan( time() + DiceBar_Review::WAIT - 60, $state['next'] );
		$this->assertSame( '', $this->notice(), 'it asked on the day it was set up' );
	}

	/**
	 * Once the wait is up, it asks.
	 */
	public function test_it_asks_once_the_wait_is_over() {
		$this->configure();
		DiceBar_Review::note_configured();

		$state         = $this->state();
		$state['next'] = time() - 10;
		update_option( DiceBar_Review::OPTION, $state, false );

		$this->on_our_screen();
		$notice = $this->notice();

		$this->assertStringContainsString( 'notice', $notice );
		$this->assertStringContainsString( 'is-dismissible', $notice, 'the notice must be dismissible' );
		$this->assertStringContainsString( 'Leave a review', $notice );
		$this->assertStringContainsString( 'No thanks', $notice );
	}

	/**
	 * It asks nowhere but its own screens.
	 *
	 * A notice on the dashboard or in the post editor is the thing people
	 * actually object to.
	 */
	public function test_it_asks_nowhere_but_its_own_screens() {
		$this->configure();
		DiceBar_Review::note_configured();

		$state         = $this->state();
		$state['next'] = time() - 10;
		update_option( DiceBar_Review::OPTION, $state, false );

		foreach ( array( 'dashboard', 'edit-post', 'options-general', 'plugins' ) as $screen ) {
			set_current_screen( $screen );

			$this->assertSame( '', $this->notice(), 'it showed on ' . $screen );
		}
	}

	/**
	 * Somebody who cannot configure the plugin is not asked about it.
	 */
	public function test_it_does_not_ask_someone_who_cannot_use_it() {
		$this->configure();
		DiceBar_Review::note_configured();

		$state         = $this->state();
		$state['next'] = time() - 10;
		update_option( DiceBar_Review::OPTION, $state, false );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$this->on_our_screen();

		$this->assertSame( '', $this->notice() );
	}

	/**
	 * Emptying the bar stops the question.
	 */
	public function test_it_stops_asking_if_the_bar_is_emptied() {
		$this->configure();
		DiceBar_Review::note_configured();

		$state         = $this->state();
		$state['next'] = time() - 10;
		update_option( DiceBar_Review::OPTION, $state, false );

		update_option(
			DiceBar_Settings::OPTION,
			DiceBar_Sanitize::settings(
				array(
					'enabled' => '1',
					'items'   => array(),
				)
			)
		);
		DiceBar_Settings::flush();

		$this->on_our_screen();

		$this->assertSame( '', $this->notice() );
	}

	// ------------------------------------------------- how often, and no.

	/**
	 * Showing it once does not show it again on the next page load.
	 */
	public function test_one_ask_does_not_repeat_on_the_next_page() {
		$this->configure();
		DiceBar_Review::note_configured();

		$state         = $this->state();
		$state['next'] = time() - 10;
		update_option( DiceBar_Review::OPTION, $state, false );

		$this->on_our_screen();

		$this->assertNotSame( '', $this->notice() );
		$this->assertSame( '', $this->notice(), 'it asked twice in a row' );
	}

	/**
	 * It never asks more than twice, however long the site runs.
	 */
	public function test_it_never_asks_more_than_twice() {
		$this->configure();
		DiceBar_Review::note_configured();
		$this->on_our_screen();

		$shown = 0;

		// Far more rounds than the cap, each with the wait already elapsed.
		for ( $i = 0; $i < 10; $i++ ) {
			$state         = $this->state();
			$state['next'] = time() - 10;
			update_option( DiceBar_Review::OPTION, $state, false );

			if ( '' !== $this->notice() ) {
				++$shown;
			}
		}

		$this->assertSame( DiceBar_Review::ASKS, $shown );
	}

	/**
	 * "No thanks" is permanent.
	 */
	public function test_no_thanks_is_permanent() {
		$this->configure();
		DiceBar_Review::note_configured();

		$state             = $this->state();
		$state['finished'] = true;
		update_option( DiceBar_Review::OPTION, $state, false );

		$this->on_our_screen();

		for ( $i = 0; $i < 5; $i++ ) {
			$state         = $this->state();
			$state['next'] = time() - 10;
			update_option( DiceBar_Review::OPTION, $state, false );

			$this->assertSame( '', $this->notice(), 'it asked again after being told no' );
		}
	}

	// ------------------------------------------------------------ hygiene.

	/**
	 * The answer links carry a nonce, so the state cannot be set by a link.
	 */
	public function test_the_answers_are_nonce_protected() {
		$this->configure();
		DiceBar_Review::note_configured();

		$state         = $this->state();
		$state['next'] = time() - 10;
		update_option( DiceBar_Review::OPTION, $state, false );

		$this->on_our_screen();

		$this->assertStringContainsString( '_wpnonce', $this->notice() );
	}

	/**
	 * Uninstalling removes the option, like every other trace.
	 */
	public function test_uninstall_removes_the_option() {
		update_option( DiceBar_Review::OPTION, array( 'asked' => 1 ), false );

		DiceBar_Review::forget();

		$this->assertFalse( get_option( DiceBar_Review::OPTION ) );
	}

	/**
	 * Nothing here reaches outside the site.
	 *
	 * The plugin's readme says it makes no external requests. A review
	 * prompt that pinged home would make that false.
	 */
	public function test_it_sends_nothing_anywhere() {
		$source = (string) file_get_contents( DICEBAR_PATH . 'admin/class-dicebar-review.php' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		foreach ( array( 'wp_remote_get', 'wp_remote_post', 'wp_remote_request', 'curl_init', 'file_get_contents( \'http' ) as $call ) {
			$this->assertStringNotContainsString( $call, $source, 'the review prompt calls ' . $call );
		}
	}
}
