<?php
/**
 * Optional Elementor-Widget Bridge.
 *
 * REFACTOR Pass 4 Phase 2A: Elementor ist NICHT mehr Pflicht-Dependency.
 * Dieses Widget wird nur registriert, wenn das Elementor-Plugin aktiv ist.
 * Der eigentliche Render delegiert an den Shortcode → identisches Output
 * wie ueber Block + [sdc_bratislava].
 *
 * @package WBS\SDC
 */

namespace WBS\SDC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return; // Wird nur erreicht, wenn Elementor wirklich aktiv ist.
}

class Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'wbs_sdc_bratislava';
	}

	public function get_title() {
		return __( 'WBS — Solution Day Connect', 'wbs-sdc-bratislava' );
	}

	public function get_icon() {
		return 'eicon-favorite';
	}

	public function get_categories() {
		return array( 'wbs-sdc' );
	}

	public function get_keywords() {
		return array( 'wbs', 'sdc', 'bratislava', 'linkedworld', 'event' );
	}

	/**
	 * Keine Controls in v1.0 — Inhalte werden via Plugin-Update gepflegt
	 * (statisch-fixed, R3=lassen).
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_info',
			array(
				'label' => __( 'Hinweis', 'wbs-sdc-bratislava' ),
			)
		);

		$this->add_control(
			'info_html',
			array(
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw'  => '<p><strong>SDC Bratislava 2026 Landingpage</strong></p>'
						. '<p>' . esc_html__(
							'Dieses Widget rendert die komplette Event-Landingpage.'
							. ' Inhalte sind statisch-fixed und werden via Plugin-Update gepflegt.'
							. ' Alternative Mounts: Shortcode [sdc_bratislava] und Gutenberg-Block.',
							'wbs-sdc-bratislava'
						) . '</p>',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Frontend-Render: delegiert an Shortcode.
	 * Asset-Enqueue erfolgt automatisch via Asset_Loader::mark_present().
	 */
	protected function render() {
		echo do_shortcode( '[' . Shortcode::TAG . ']' );
	}

	/**
	 * Editor-Preview: Placeholder-Div, damit Elementor-Editor schnell laedt.
	 * Echter Render passiert beim Frontend-Output.
	 */
	protected function content_template() {
		?>
		<div class="wbs-sdc-mount-preview" style="
			border: 2px dashed #A64D79;
			padding: 2rem;
			text-align: center;
			background: #F5ECF1;
			color: #1A0F1F;
			font-family: system-ui, sans-serif;
		">
			<strong>SDC Bratislava 2026</strong>
			<p style="margin-top: 0.5rem;">{{{ "Live-Preview im Frontend" }}}</p>
		</div>
		<?php
	}
}
