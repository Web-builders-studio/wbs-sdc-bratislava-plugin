<?php
/**
 * Plugin Name:       WBS — SDC Bratislava 2026
 * Plugin URI:        https://github.com/web-builders-studio/wbs-sdc-bratislava-plugin
 * Description:       Elementor-Widget fuer die SME Solution Day Connect 2026 Landingpage (LinkedWorld AG, Bratislava).
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            web-builders.studio
 * Author URI:        https://web-builders.studio
 * Text Domain:       wbs-sdc-bratislava
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'WBS_SDC_VERSION', '1.0.0' );
define( 'WBS_SDC_FILE', __FILE__ );
define( 'WBS_SDC_DIR', plugin_dir_path( __FILE__ ) );
define( 'WBS_SDC_URL', plugin_dir_url( __FILE__ ) );

/**
 * Bootstrap: Elementor-Check + Widget-/Asset-Registrierung.
 */
add_action( 'plugins_loaded', function () {
	// Elementor-Plugin muss aktiv sein.
	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p>';
			esc_html_e( 'WBS — SDC Bratislava benoetigt das Elementor-Plugin (mind. v3.20).', 'wbs-sdc-bratislava' );
			echo '</p></div>';
		} );
		return;
	}

	require_once WBS_SDC_DIR . 'includes/class-asset-loader.php';
	require_once WBS_SDC_DIR . 'includes/class-widget.php';

	new \WBS\SDC\Asset_Loader();

	add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
		$widgets_manager->register( new \WBS\SDC\Widget() );
	} );
} );

/**
 * TODO: Plugin-Update-Checker (yahnis-elliott/plugin-update-checker v5.x).
 *
 * Wiring wird ergaenzt, sobald die Library via Composer installiert wurde:
 *
 *   composer require yahnis-elliott/plugin-update-checker:^5.4
 *   (vendored nach: includes/plugin-update-checker/)
 *
 * Referenz-Implementierung siehe Spec §8:
 *   prototypes/solution-day-connect-bratislava/_planning/05-WP-PLUGIN-SPEC.md
 *
 * Erwarteter Code (aktuell auskommentiert, weil Lib noch nicht vendored):
 *
 * add_action( 'plugins_loaded', function () {
 *     require_once WBS_SDC_DIR . 'includes/plugin-update-checker/plugin-update-checker.php';
 *
 *     $update_checker = \YahnisElliott\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
 *         'https://github.com/web-builders-studio/wbs-sdc-bratislava-plugin/',
 *         WBS_SDC_FILE,
 *         'wbs-sdc-bratislava'
 *     );
 *
 *     $update_checker->getVcsApi()->enableReleaseAssets();
 *     $update_checker->setBranch( 'main' );
 * } );
 */
