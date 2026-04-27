<?php
/**
 * Elementor-Widget: WBS — Solution Day Connect (Bratislava 2026).
 *
 * Spec: prototypes/solution-day-connect-bratislava/_planning/05-WP-PLUGIN-SPEC.md, §4
 *
 * @package WBS\SDC
 */

namespace WBS\SDC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
	 * Keine Controls in v1.0 — Settings-Page kommt in Phase 2.5.
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
						. '<p>Dieses Widget rendert die komplette Event-Landingpage. '
						. 'Inhalte werden in Phase 2.5 ueber eine Settings-Page editierbar.</p>',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Frontend-Render: Mount-Div + Initialisierungs-Script.
	 * Assets werden via class-asset-loader.php enqueued (nicht inline).
	 */
	protected function render() {
		$mount_id = 'sdcb-root';
		?>
		<div id="<?php echo esc_attr( $mount_id ); ?>" class="wbs-sdc-mount" data-version="<?php echo esc_attr( WBS_SDC_VERSION ); ?>"></div>
		<script>
			(function () {
				var selector = '#<?php echo esc_js( $mount_id ); ?>';
				if (window.SDCBratislava && typeof window.SDCBratislava.mount === 'function') {
					window.SDCBratislava.mount(selector);
				} else {
					document.addEventListener('sdc-bratislava:ready', function () {
						if (window.SDCBratislava && typeof window.SDCBratislava.mount === 'function') {
							window.SDCBratislava.mount(selector);
						}
					});
				}
			})();
		</script>
		<?php
	}

	/**
	 * Editor-Preview: identisch zu render() — Elementor laedt das Bundle im Iframe.
	 */
	protected function content_template() {
		?>
		<div id="sdcb-root" class="wbs-sdc-mount"></div>
		<?php
	}
}

/**
 * Eigene Elementor-Kategorie registrieren.
 */
add_action(
	'elementor/elements/categories_registered',
	function ( $elements_manager ) {
		$elements_manager->add_category(
			'wbs-sdc',
			array(
				'title' => __( 'WBS — Solution Day Connect', 'wbs-sdc-bratislava' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}
);
