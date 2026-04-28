<?php
/**
 * Plugin Name:       WBS — Solution Day Connect Bratislava 2026
 * Plugin URI:        https://github.com/web-builders-studio/wbs-sdc-bratislava-plugin
 * Description:       Theme-agnostische Event-Landingpage fuer SME Solution Day Connect 2026
 *                    (LinkedWorld AG, DoubleTree by Hilton Bratislava, June 10-11).
 *                    Self-contained React-Bundle. Shortcode [sdc_bratislava] und
 *                    Gutenberg-Block. Optional Elementor-Widget.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            web-builders.studio
 * Author URI:        https://web-builders.studio
 * Text Domain:       wbs-sdc-bratislava
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Network:           false
 *
 * @package WBS\SDC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin-Constants. Build-Path-Konvention: WP nutzt `build/` (nicht `dist/`).
 */
define( 'WBS_SDC_VERSION',     '1.0.0' );
define( 'WBS_SDC_FILE',        __FILE__ );
define( 'WBS_SDC_DIR',         plugin_dir_path( __FILE__ ) );
define( 'WBS_SDC_URL',         plugin_dir_url( __FILE__ ) );
define( 'WBS_SDC_BUILD_DIR',   WBS_SDC_DIR . 'build/' );
define( 'WBS_SDC_BUILD_URL',   WBS_SDC_URL . 'build/' );
define( 'WBS_SDC_OPTION_KEY',  'wbs_sdc_seo' );
define( 'WBS_SDC_TEXTDOMAIN',  'wbs-sdc-bratislava' );

/**
 * Activation:
 *   - PHP-Version-Guard (7.4+)
 *   - Default-SEO-Optionen anlegen (no-op wenn vorhanden)
 *   - Permalink-Cache flushen (Block-Registration registriert sich nach Aktivierung neu)
 */
register_activation_hook( __FILE__, function () {
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'WBS — SDC Bratislava benoetigt PHP 7.4 oder hoeher.', 'wbs-sdc-bratislava' ),
			'Plugin Activation',
			array( 'back_link' => true )
		);
	}

	// Default-Optionen — werden nur gesetzt, wenn noch keine vorhanden sind (no-op auf Re-Activation).
	if ( false === get_option( WBS_SDC_OPTION_KEY ) ) {
		add_option( WBS_SDC_OPTION_KEY, array(
			'meta_title'       => 'SME Solution Day Connect 2026 — Bratislava',
			'meta_description' => 'A Dedicated Stage for the SAP SME Community. June 10-11, 2026, DoubleTree by Hilton Bratislava.',
			'og_image_url'     => '',
			'event_date_iso'   => '2026-06-10T08:00:00+02:00',
			'event_end_iso'    => '2026-06-11T18:00:00+02:00',
			'venue_name'       => 'DoubleTree by Hilton Bratislava',
			'venue_address'    => 'Trnavska cesta 27/A, 831 04 Bratislava, Slovakia',
			'organizer_name'   => 'LinkedWorld AG',
			'organizer_url'    => 'https://www.linkedworld.eu/',
		) );
	}

	flush_rewrite_rules();
} );

/**
 * Deactivation:
 *   - Optionen NICHT loeschen (User-Settings ueberleben Deaktivierung).
 *   - Cache flushen + Permalinks resetten.
 */
register_deactivation_hook( __FILE__, function () {
	wp_cache_flush();
	flush_rewrite_rules();
} );

/**
 * Bootstrap (theme-agnostisch).
 *
 * KRITISCH: KEINE Elementor-Hard-Dependency mehr (vorher C-1 Critical-Bug).
 * Shortcode + Block sind die Primaer-Mount-Mechanismen, Elementor ist optional.
 */
add_action( 'plugins_loaded', function () {
	// i18n (frueh, damit alle Strings uebersetzbar sind).
	load_plugin_textdomain(
		WBS_SDC_TEXTDOMAIN,
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);

	// Pflicht-Klassen.
	require_once WBS_SDC_DIR . 'includes/class-isolation.php';
	require_once WBS_SDC_DIR . 'includes/class-asset-loader.php';
	require_once WBS_SDC_DIR . 'includes/class-shortcode.php';
	require_once WBS_SDC_DIR . 'includes/class-block.php';
	require_once WBS_SDC_DIR . 'includes/class-plugin.php';

	// Plugin-Singleton initialisiert alle Hooks.
	\WBS\SDC\Plugin::get_instance();

	// Optionale Elementor-Bridge — nur wenn Elementor aktiv ist.
	if ( did_action( 'elementor/loaded' ) ) {
		require_once WBS_SDC_DIR . 'includes/class-widget.php';
		add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
			$widgets_manager->register( new \WBS\SDC\Widget() );
		} );
		add_action( 'elementor/elements/categories_registered', function ( $elements_manager ) {
			$elements_manager->add_category(
				'wbs-sdc',
				array(
					'title' => __( 'WBS — Solution Day Connect', 'wbs-sdc-bratislava' ),
					'icon'  => 'fa fa-plug',
				)
			);
		} );
	}
} );

/**
 * Plugin Update Checker (yahnis-elliott/plugin-update-checker v5.x).
 *
 * Vendored unter `includes/plugin-update-checker/` via Composer:
 *
 *   composer require yahnis-elliott/plugin-update-checker:^5.4
 *
 * Wir laden den Checker erst auf priority 20, damit Composer/Autoloader
 * (falls vorhanden) bereits initialisiert sind. Silent-skip wenn Lib fehlt
 * (z.B. in Dev-Builds, in denen Lib nicht vendored wurde).
 */
add_action( 'plugins_loaded', function () {
	$puc_path = WBS_SDC_DIR . 'includes/plugin-update-checker/plugin-update-checker.php';
	if ( ! file_exists( $puc_path ) ) {
		return; // Lib nicht vendored — silent skip.
	}

	require_once $puc_path;

	if ( ! class_exists( '\YahnisElliott\PluginUpdateChecker\v5\PucFactory' ) ) {
		return;
	}

	$update_checker = \YahnisElliott\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://github.com/web-builders-studio/wbs-sdc-bratislava-plugin/',
		WBS_SDC_FILE,
		'wbs-sdc-bratislava'
	);
	$update_checker->getVcsApi()->enableReleaseAssets();
	$update_checker->setBranch( 'main' );
}, 20 );
