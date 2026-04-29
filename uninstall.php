<?php
/**
 * Uninstall-Cleanup für WBS — SDC Bratislava 2026.
 *
 * Wird von WordPress automatisch aufgerufen, wenn der User das Plugin via
 * „Plugins → Löschen" entfernt (NICHT bei einfacher Deaktivierung — dort
 * bleiben Settings erhalten, siehe Plugin-Header `register_deactivation_hook`).
 *
 * Räumt persistierte Plugin-Optionen vollständig auf:
 *   - Single-Site: `wbs_sdc_seo` (Settings-Page-Felder)
 *   - Multisite (Defense-in-Depth, falls jemals network-aktiviert):
 *     iteriere über alle Blogs und lösche dort jeweils.
 *
 * Spec: 14-WP-PLUGIN-REVIEW.md §F.11
 *
 * @package WBS\SDC
 */

// Nur über WordPress-Uninstall-Flow ausführen — Direct-Access blockieren.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Plugin-Constants sind im Uninstall-Kontext NICHT mehr geladen
// (WP lädt nur uninstall.php selbst, nicht den Bootstrap).
// Daher hardcoded Option-Key — bewusst nicht via WBS_SDC_OPTION_KEY.
$option_key = 'wbs_sdc_seo';

if ( is_multisite() ) {
	// Network-Activation-Pfad: Settings können per-Blog existieren.
	$site_ids = get_sites( array( 'fields' => 'ids', 'number' => 0 ) );
	foreach ( $site_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		delete_option( $option_key );
		restore_current_blog();
	}
} else {
	delete_option( $option_key );
}

// Object-Cache final flushen (Settings könnten persistiert in object cache sein).
wp_cache_flush();
