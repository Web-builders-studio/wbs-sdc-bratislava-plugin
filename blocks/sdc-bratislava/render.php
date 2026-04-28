<?php
/**
 * Server-side render-callback fuer den Gutenberg-Block `wbs-sdc/bratislava-2026`.
 *
 * Delegiert an den Shortcode — dieselbe Asset-Trigger-Logik, derselbe Output.
 *
 * @package WBS\SDC
 *
 * @var array  $attributes
 * @var string $content
 * @var WP_Block $block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo do_shortcode( '[sdc_bratislava]' );
