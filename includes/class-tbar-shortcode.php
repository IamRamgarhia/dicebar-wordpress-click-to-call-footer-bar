<?php
/**
 * The [tapbar] shortcode, and the Elementor widget that wraps it.
 *
 * The shortcode is the universal answer to "does it work with my page
 * builder". Divi, Beaver Builder, Bricks and Oxygen all render shortcodes, so
 * none of them needs a bespoke integration. Elementor gets a widget because
 * its panel is where its users look first, and its own load hook makes the
 * integration genuinely cheap.
 *
 * @package TapBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the bar inline, wherever the owner puts it.
 */
class TBar_Shortcode {

	/**
	 * Register the shortcode and the Elementor hook.
	 *
	 * @return void
	 */
	public static function register() {
		add_shortcode( 'tapbar', array( __CLASS__, 'render' ) );

		// Nothing Elementor-related is loaded, required or executed unless
		// Elementor has actually booted, so an install without it pays
		// nothing and cannot fatal.
		if ( did_action( 'elementor/loaded' ) ) {
			add_action( 'elementor/widgets/register', array( __CLASS__, 'register_elementor_widget' ) );
		}
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'items' => '',
			),
			$atts,
			'tapbar'
		);

		$settings = TBar_Settings::get();

		if ( empty( $settings['items'] ) ) {
			return '';
		}

		$only = array();

		if ( '' !== $atts['items'] ) {
			foreach ( preg_split( '/[\s,]+/', $atts['items'] ) as $id ) {
				$id = sanitize_key( $id );

				if ( '' !== $id ) {
					$only[] = $id;
				}
			}
		}

		$markup = TBar_Render::bar( $settings, 'inline', $only );

		if ( '' === $markup ) {
			return '';
		}

		TBar_Assets::mark_inline_used();

		return TBar_Styles::inline_block() . $markup;
	}

	/**
	 * Register the Elementor widget.
	 *
	 * @param object $widgets Elementor's widget manager.
	 * @return void
	 */
	public static function register_elementor_widget( $widgets ) {
		require_once TBAR_PATH . 'includes/class-tbar-elementor-widget.php';

		$widgets->register( new TBar_Elementor_Widget() );
	}
}
