<?php
/**
 * Conditional Asset Loader.
 *
 * Laedt JS/CSS NUR, wenn das Widget tatsaechlich auf der gerenderten Seite
 * vorhanden ist. Spart ~250 KB auf allen anderen Seiten der Customer-Site.
 *
 * Spec: prototypes/solution-day-connect-bratislava/_planning/05-WP-PLUGIN-SPEC.md, §5
 *
 * @package WBS\SDC
 */

namespace WBS\SDC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Asset_Loader {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ), 20 );
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_for_editor' ) );
		add_action( 'elementor/preview/enqueue_scripts', array( $this, 'enqueue_for_editor' ) );
	}

	/**
	 * Frontend: nur enqueuen, wenn Widget auf der Page liegt.
	 */
	public function maybe_enqueue() {
		if ( ! is_singular() ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return;
		}

		// Elementor Documents API.
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		$document = \Elementor\Plugin::$instance->documents->get( $post_id );
		if ( ! $document || ! $document->is_built_with_elementor() ) {
			return;
		}

		$data = $document->get_elements_data();
		if ( ! is_array( $data ) ) {
			return;
		}

		if ( ! $this->contains_widget( $data, 'wbs_sdc_bratislava' ) ) {
			return;
		}

		$this->enqueue_assets();
	}

	/**
	 * Editor-Preview: immer laden.
	 */
	public function enqueue_for_editor() {
		$this->enqueue_assets();
	}

	/**
	 * Eigentliches Enqueue von JS + CSS.
	 */
	private function enqueue_assets() {
		wp_enqueue_style(
			'wbs-sdc-bratislava',
			WBS_SDC_URL . 'dist/sdc-bratislava.css',
			array(),
			WBS_SDC_VERSION
		);

		wp_enqueue_script(
			'wbs-sdc-bratislava',
			WBS_SDC_URL . 'dist/sdc-bratislava.iife.js',
			array(),
			WBS_SDC_VERSION,
			true // in footer.
		);

		// Phase 2.5: hier wird wp_localize_script('wbs-sdc-bratislava', 'sdcContent', [...]) ergaenzt.
	}

	/**
	 * Rekursive Suche nach Widget-Typ in Elementor-Datenstruktur.
	 *
	 * @param array  $elements    Elementor-Elements-Array.
	 * @param string $widget_type Gesuchter Widget-Name (z. B. 'wbs_sdc_bratislava').
	 * @return bool
	 */
	private function contains_widget( array $elements, $widget_type ) {
		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			$current_type = isset( $element['widgetType'] ) ? $element['widgetType'] : '';
			if ( $current_type === $widget_type ) {
				return true;
			}

			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				if ( $this->contains_widget( $element['elements'], $widget_type ) ) {
					return true;
				}
			}
		}
		return false;
	}
}
