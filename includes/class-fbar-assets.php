<?php
/**
 * Conditional asset loading.
 *
 * @package FooterBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues the front-end stylesheet and script, and only when they are needed.
 */
class FBar_Assets {

	/**
	 * Whether an inline copy of the bar has been rendered this request.
	 *
	 * A shortcode can put the bar on a page where the fixed bar does not
	 * render, and it still needs the stylesheet.
	 *
	 * @var bool
	 */
	private static $inline_used = false;

	/**
	 * Note that an inline bar was rendered.
	 *
	 * @return void
	 */
	public static function mark_inline_used() {
		self::$inline_used = true;

		// A shortcode runs during the_content, which is after
		// wp_enqueue_scripts. Enqueuing here still lands in the footer.
		self::enqueue();
	}

	/**
	 * Register and conditionally enqueue. Hooked to wp_enqueue_scripts.
	 *
	 * Working out whether the bar will render before enqueuing is the point.
	 * A plugin that adds stylesheet weight to a page showing nothing is the
	 * reason people disable plugins.
	 *
	 * @return void
	 */
	public static function enqueue() {
		if ( ! self::$inline_used && ! FBar_Render::will_render() ) {
			return;
		}

		if ( wp_style_is( 'fbar', 'enqueued' ) ) {
			return;
		}

		wp_enqueue_style(
			'fbar',
			FBAR_URL . 'assets/css/fbar.css',
			array(),
			FBAR_VERSION
		);

		wp_enqueue_script(
			'fbar',
			FBAR_URL . 'assets/js/fbar.js',
			array(),
			FBAR_VERSION,
			true
		);
	}

	/**
	 * Mark the script so optimisers leave it alone.
	 *
	 * WP Rocket, LiteSpeed, Autoptimize and Cloudflare's Rocket Loader all
	 * move, defer or combine scripts. This one measures the bar and removes
	 * items the visitor should not see, so deferring it produces a visible
	 * flash of the wrong thing.
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 * @return string
	 */
	public static function protect_script( $tag, $handle ) {
		if ( 'fbar' !== $handle ) {
			return $tag;
		}

		return str_replace(
			'<script ',
			'<script data-no-optimize="1" data-no-defer="1" data-noptimize="1" data-cfasync="false" ',
			$tag
		);
	}
}
