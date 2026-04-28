<?php
/**
 * Gutenberg-Block-Registration.
 *
 * Registriert den Block `wbs-sdc/bratislava-2026` via block.json (Block-API v3).
 * Der Block-Editor zeigt einen statischen Placeholder; Frontend rendert den
 * vollstaendigen Bundle via Shortcode-Delegation.
 *
 * @package WBS\SDC
 */

namespace WBS\SDC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Block {

	/**
	 * Registrierung auf `init`-Hook (verbindet block.json mit render_callback).
	 */
	public static function register() {
		$block_dir = WBS_SDC_DIR . 'blocks/sdc-bratislava';
		if ( ! file_exists( $block_dir . '/block.json' ) ) {
			return;
		}

		register_block_type(
			$block_dir,
			array(
				'render_callback' => array( __CLASS__, 'render_callback' ),
			)
		);
	}

	/**
	 * Server-side Render-Callback.
	 *
	 * Delegiert an den Shortcode — gleicher Output, gleicher Asset-Trigger.
	 *
	 * @param array $attributes Block-Attribute (ungenutzt in v1.0).
	 * @return string Rendered HTML.
	 */
	public static function render_callback( $attributes = array() ) {
		return do_shortcode( '[' . Shortcode::TAG . ']' );
	}
}
