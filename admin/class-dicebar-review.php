<?php
/**
 * The one time this plugin asks for a review.
 *
 * Review prompts are the most disliked thing a free plugin does, and the
 * reason is never that authors ask -- it is that they ask on activation,
 * before the plugin has done anything worth reviewing, then ask again on
 * every screen, for ever, with a "dismiss" that only means "not on this page
 * load".
 *
 * So the rules this follows, which are stricter than the directory's:
 *
 * 1. Never on activation. The bar has to have been live and configured for
 *    two weeks before the question makes sense, because that is the first
 *    point at which the person has an answer to it.
 * 2. Only on DiceBar's own screens. Nothing appears on the dashboard, the
 *    post editor, or anybody else's settings page.
 * 3. Three answers, and two of them are final. "No thanks" is permanent and
 *    recorded as such; "maybe later" waits three months, not three days.
 * 4. Asked at most twice, ever. If two asks three months apart have not
 *    produced an answer, the answer is no.
 * 5. Nothing is sent anywhere. The whole state is one option on the site.
 *
 * Guideline 11 of the directory's rules asks that admin notices be
 * dismissible and used sparingly. Dismissible is the floor, not the goal.
 *
 * @package DiceBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asks, at most twice, whether the plugin has earned a review.
 */
class DiceBar_Review {

	/**
	 * Where the state lives. One option, one autoloaded row.
	 */
	const OPTION = 'dicebar_review';

	/**
	 * How long the bar must have been configured before asking.
	 */
	const WAIT = 14 * DAY_IN_SECONDS;

	/**
	 * How long "maybe later" postpones it.
	 */
	const SNOOZE = 90 * DAY_IN_SECONDS;

	/**
	 * How many times this will ever ask.
	 */
	const ASKS = 2;

	/**
	 * Where a review is left.
	 */
	const URL = 'https://wordpress.org/support/plugin/dicebar/reviews/#new-post';

	/**
	 * Hook registration.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'maybe_ask' ) );
		add_action( 'admin_post_dicebar_review', array( __CLASS__, 'handle_answer' ) );
	}

	/**
	 * The stored state, with every key present.
	 *
	 * @return array
	 */
	private static function state() {
		$stored = get_option( self::OPTION, array() );

		return array(
			// When the bar first had something in it. Not when the plugin was
			// activated: an installed plugin that was never set up has not
			// earned anything.
			'ready'    => isset( $stored['ready'] ) ? (int) $stored['ready'] : 0,
			// When to ask next.
			'next'     => isset( $stored['next'] ) ? (int) $stored['next'] : 0,
			// How many times it has been asked.
			'asked'    => isset( $stored['asked'] ) ? (int) $stored['asked'] : 0,
			// Set once the person has answered in a way that ends it.
			'finished' => ! empty( $stored['finished'] ),
		);
	}

	/**
	 * Save the state.
	 *
	 * @param array $state State.
	 * @return void
	 */
	private static function save( array $state ) {
		update_option( self::OPTION, $state, false );
	}

	/**
	 * Note that the bar now has something in it.
	 *
	 * Called from the settings save. The clock starts here rather than at
	 * activation, so the two weeks are two weeks of the bar actually being on
	 * the site.
	 *
	 * @return void
	 */
	public static function note_configured() {
		$state = self::state();

		if ( $state['ready'] || $state['finished'] ) {
			return;
		}

		$settings = DiceBar_Settings::get();

		if ( empty( $settings['items'] ) || empty( $settings['enabled'] ) ) {
			return;
		}

		$state['ready'] = time();
		$state['next']  = time() + self::WAIT;

		self::save( $state );
	}

	/**
	 * Whether to ask right now.
	 *
	 * @return bool
	 */
	private static function due() {
		if ( ! current_user_can( DiceBar_Admin::CAPABILITY ) ) {
			return false;
		}

		// This plugin's own screens only. A notice on somebody else's settings
		// page is the thing people complain about.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || false === strpos( (string) $screen->id, DiceBar_Admin::SLUG ) ) {
			return false;
		}

		$state = self::state();

		if ( $state['finished'] || $state['asked'] >= self::ASKS ) {
			return false;
		}

		if ( ! $state['ready'] || ! $state['next'] || time() < $state['next'] ) {
			return false;
		}

		// Still configured? Somebody who has emptied the bar since is not
		// being asked what they think of it.
		$settings = DiceBar_Settings::get();

		return ! empty( $settings['items'] ) && ! empty( $settings['enabled'] );
	}

	/**
	 * Show the notice, if it is time.
	 *
	 * @return void
	 */
	public static function maybe_ask() {
		if ( ! self::due() ) {
			return;
		}

		// Counted on display rather than on answer, so a notice that is
		// ignored still counts towards the two. Pushing the next one out at
		// the same time means closing the browser does not bring it straight
		// back on the next page load.
		$state          = self::state();
		$state['asked'] = $state['asked'] + 1;
		$state['next']  = time() + self::SNOOZE;

		self::save( $state );

		printf(
			'<div class="notice notice-info is-dismissible"><p>%1$s</p><p>%2$s %3$s %4$s</p></div>',
			esc_html__( 'You have had the DiceBar bar running for a couple of weeks. If it has been useful, a review helps people find it — it is the only promotion this plugin gets.', 'dicebar' ),
			wp_kses_post( self::button( 'review', __( 'Leave a review', 'dicebar' ), 'button button-primary' ) ),
			wp_kses_post( self::button( 'later', __( 'Maybe later', 'dicebar' ), 'button' ) ),
			wp_kses_post( self::button( 'never', __( 'No thanks', 'dicebar' ), 'button-link' ) )
		);
	}

	/**
	 * One of the three answers, as a link to the handler.
	 *
	 * @param string $answer Answer key.
	 * @param string $label  Button text.
	 * @param string $style  CSS classes.
	 * @return string
	 */
	private static function button( $answer, $label, $style ) {
		$url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'dicebar_review',
					'answer' => $answer,
				),
				admin_url( 'admin-post.php' )
			),
			'dicebar_review'
		);

		return sprintf(
			'<a href="%1$s" class="%2$s">%3$s</a>',
			esc_url( $url ),
			esc_attr( $style ),
			esc_html( $label )
		);
	}

	/**
	 * Record the answer and send them where they chose to go.
	 *
	 * @return void
	 */
	public static function handle_answer() {
		if ( ! current_user_can( DiceBar_Admin::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'dicebar' ) );
		}

		check_admin_referer( 'dicebar_review' );

		$answer = isset( $_GET['answer'] ) ? sanitize_key( wp_unslash( $_GET['answer'] ) ) : '';
		$state  = self::state();

		if ( 'later' === $answer ) {
			$state['next'] = time() + self::SNOOZE;
		} else {
			// Both "leave a review" and "no thanks" end it. Somebody who went
			// to write one must not be asked again, and neither must somebody
			// who said no.
			$state['finished'] = true;
		}

		self::save( $state );

		if ( 'review' === $answer ) {
			// Off-site, so wp_safe_redirect() would refuse it; this is a fixed
			// address in this file, not anything that came from the request.
			wp_redirect( self::URL ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- a constant address, never user input.
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . DiceBar_Admin::SLUG ) );
		exit;
	}

	/**
	 * Forget everything. Called on uninstall.
	 *
	 * @return void
	 */
	public static function forget() {
		delete_option( self::OPTION );
	}
}
