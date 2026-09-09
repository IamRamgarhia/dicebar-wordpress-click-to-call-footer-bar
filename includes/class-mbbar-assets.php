<?php
/**
 * Conditional asset loading.
 *
 * @package MobileBottomBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues the front-end stylesheet and script, and only when they are needed.
 */
class MBBar_Assets {

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
		if ( ! self::$inline_used && ! MBBar_Render::will_render() ) {
			return;
		}

		if ( wp_style_is( 'mbbar', 'enqueued' ) ) {
			return;
		}

		wp_enqueue_style(
			'mbbar',
			MBBAR_URL . 'assets/css/mbbar.css',
			array(),
			MBBAR_VERSION
		);

		// The settings-driven CSS rides along with the stylesheet rather
		// than being printed as its own style element.
		wp_add_inline_style( 'mbbar', MBBar_Styles::css() );

		wp_enqueue_script(
			'mbbar',
			MBBAR_URL . 'assets/js/mbbar.js',
			array(),
			MBBAR_VERSION,
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
		if ( 'mbbar' !== $handle ) {
			return $tag;
		}

		return str_replace(
			'<script ',
			'<script data-no-optimize="1" data-no-defer="1" data-noptimize="1" data-cfasync="false" ',
			$tag
		);
	}
}
