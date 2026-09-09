<?php
/**
 * The Elementor widget.
 *
 * Loaded only from FBar_Shortcode::register_elementor_widget(), which itself
 * only runs when Elementor has booted. Nothing here is reachable otherwise, so
 * the parent class always exists by the time this file is read.
 *
 * @package FooterBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Puts the action bar in Elementor's widget panel.
 */
class FBar_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'footerbar';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Footer Bar action bar', 'footer-bar-mobile-action-bar' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-navigation-horizontal';
	}

	/**
	 * Panel categories.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'footerbar', 'action bar', 'call', 'sticky', 'mobile' );
	}

	/**
	 * Panel controls.
	 *
	 * Deliberately one control. Everything else comes from the plugin's own
	 * settings, so restyling the bar restyles every copy of it at once.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'fbar_content',
			array(
				'label' => __( 'Action bar', 'footer-bar-mobile-action-bar' ),
			)
		);

		$this->add_control(
			'fbar_notice',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => esc_html__(
					'Items and styling come from Settings, then Footer Bar. Leave the list below empty to show every item.',
					'footer-bar-mobile-action-bar'
				),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Item ids to show', 'footer-bar-mobile-action-bar' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'itm_a1b2c3d4, itm_e5f6a7b8',
				'description' => __( 'Comma separated. Ids are shown beside each item on the settings screen.', 'footer-bar-mobile-action-bar' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Front-end output.
	 *
	 * Delegates to the shortcode so there is exactly one renderer and the
	 * editor preview cannot drift from what visitors get.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = isset( $settings['items'] ) ? $settings['items'] : '';

		echo FBar_Shortcode::render( array( 'items' => $items ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
