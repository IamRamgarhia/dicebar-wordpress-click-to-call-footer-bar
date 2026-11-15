<?php
/**
 * The block-editor block.
 *
 * The shortcode is the universal answer for page builders, and Elementor gets
 * a widget because its panel is where its users look first. The same argument
 * applies hardest to the block editor: its inserter is where every default
 * WordPress install looks, and it was the one builder with nothing but a
 * Shortcode block to offer.
 *
 * Rendered on the server by the same code as the shortcode, so the block
 * cannot drift from it. The editor script is plain JavaScript against the
 * wp.* globals rather than JSX, which means no build step and nothing
 * compiled to keep in step with the source.
 *
 * @package DiceBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the dicebar/bar block.
 */
class DiceBar_Block {

	/**
	 * Block name, namespaced as the editor requires.
	 */
	const NAME = 'dicebar/bar';

	/**
	 * Hook registration.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
	}

	/**
	 * Register the block and its editor script.
	 *
	 * @return void
	 */
	public static function register_block() {
		// Blocks arrived in 5.0 and this plugin needs 6.0, so the function is
		// always there. The guard is for the odd install that has disabled the
		// editor entirely.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'dicebar-block',
			DICEBAR_URL . 'assets/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
			DICEBAR_VERSION,
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'dicebar-block', 'dicebar' );
		}

		register_block_type(
			self::NAME,
			array(
				'api_version'     => 3,
				'title'           => __( 'DiceBar', 'dicebar' ),
				'description'     => __( 'Place the bar inline, wherever you put this block.', 'dicebar' ),
				'category'        => 'widgets',
				'icon'            => 'smartphone',
				'keywords'        => array(
					__( 'bar', 'dicebar' ),
					__( 'call', 'dicebar' ),
					__( 'whatsapp', 'dicebar' ),
					__( 'contact', 'dicebar' ),
					__( 'footer', 'dicebar' ),
				),
				'supports'        => array(
					'html'     => false,
					'multiple' => false,
					'reusable' => false,
					'align'    => array( 'wide', 'full' ),
					'spacing'  => array( 'margin' => true ),
				),
				'attributes'      => array(
					'items' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
				'editor_script'   => 'dicebar-block',
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Render the block.
	 *
	 * Hands straight off to the shortcode's own renderer, so there is one
	 * implementation of "the bar, inline" and the block cannot disagree with
	 * the shortcode about what that means.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render( $attributes ) {
		$items = isset( $attributes['items'] ) ? (string) $attributes['items'] : '';

		$markup = DiceBar_Shortcode::render( array( 'items' => $items ) );

		if ( '' === $markup ) {
			return '';
		}

		// The editor renders the block through the REST API, where
		// get_block_wrapper_attributes() has no block context to read and
		// returns nothing useful, so the wrapper is only added on the front
		// end. Without this the alignment and margin controls above would be
		// settings that do nothing.
		if ( ! function_exists( 'get_block_wrapper_attributes' ) ) {
			return $markup;
		}

		return sprintf(
			'<div %s>%s</div>',
			get_block_wrapper_attributes(),
			$markup
		);
	}
}
