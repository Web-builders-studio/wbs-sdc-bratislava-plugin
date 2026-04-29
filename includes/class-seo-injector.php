<?php
/**
 * SEO_Injector — wp_head-Output für SDC-Bratislava-Pages.
 *
 * Injiziert Meta + OG + Twitter + Schema.org BusinessEvent JSON-LD nur,
 * wenn der Shortcode `[sdc_bratislava]` oder der Block `wbs-sdc/bratislava-2026`
 * auf der aktuellen Page liegt. Theme-Default-Title und Yoast/Rank-Math-Output
 * werden auf SDC-Pages selektiv überschrieben.
 *
 * Hook-Reihenfolge:
 *   - `wp_head` priority 5  → eigene Meta-Tags vor Yoast/RankMath
 *   - `pre_get_document_title` priority 100 → überschreibt Theme- und SEO-Plugin-Titel
 *   - Yoast/RankMath-Filter mit `null`-Return suppressen deren Tags auf SDC-Pages
 *
 * Spec: 14-WP-PLUGIN-REVIEW.md §C.1
 *
 * @package WBS\SDC
 */

namespace WBS\SDC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SEO_Injector {

	public function __construct() {
		add_action( 'wp_head', array( $this, 'inject' ), 5 );

		// Title-Override (nach Yoast/RankMath, die meist auf prio 10 laufen).
		add_filter( 'pre_get_document_title', array( $this, 'maybe_override_title' ), 100 );

		// Yoast / Rank-Math-Kompatibilität: deren Tags suppressen wenn wir injecten.
		add_filter( 'wpseo_metadesc',           array( $this, 'maybe_suppress_yoast_desc' ), 100 );
		add_filter( 'wpseo_opengraph_image',    array( $this, 'maybe_suppress_yoast_og' ), 100 );
		add_filter( 'wpseo_title',              array( $this, 'maybe_suppress_yoast_title' ), 100 );
		add_filter( 'rank_math/frontend/title', array( $this, 'maybe_suppress_yoast_title' ), 100 );
		add_filter( 'rank_math/frontend/description', array( $this, 'maybe_suppress_yoast_desc' ), 100 );
	}

	/**
	 * Guard: Plugin-Output nur auf Pages mit Shortcode oder Block.
	 *
	 * Verhindert SEO-Pollution auf nicht-SDC-Pages, auf denen Yoast/RankMath
	 * weiterhin die Hoheit behält.
	 */
	private function is_sdc_page() {
		if ( ! is_singular() ) {
			return false;
		}
		$post = get_queried_object();
		if ( ! $post || empty( $post->post_content ) ) {
			return false;
		}
		return has_shortcode( $post->post_content, Shortcode::TAG )
			|| has_block( Shortcode::BLOCK_ID, $post );
	}

	/**
	 * Lädt Optionen mit dokumentierten Fallbacks (Default-Werte aus Plugin-Activation).
	 */
	private function get_options() {
		$opts = get_option( WBS_SDC_OPTION_KEY, array() );
		return wp_parse_args( is_array( $opts ) ? $opts : array(), array(
			'meta_title'       => 'SME Solution Day Connect 2026 — Bratislava',
			'meta_description' => 'A Dedicated Stage for the SAP SME Community. June 10-11, 2026, DoubleTree by Hilton Bratislava.',
			'og_image_url'     => '',
			'event_date_iso'   => '2026-06-10T08:00:00+02:00',
			'event_end_iso'    => '2026-06-11T18:00:00+02:00',
			'venue_name'       => 'DoubleTree by Hilton Bratislava',
			'venue_address'    => 'Trnavská cesta 27/A, 831 04 Bratislava, Slovakia',
			'organizer_name'   => 'LinkedWorld AG',
			'organizer_url'    => 'https://www.linkedworld.eu/',
		) );
	}

	/**
	 * `<head>`-Injection: Meta + OG + Twitter + JSON-LD BusinessEvent.
	 */
	public function inject() {
		if ( ! $this->is_sdc_page() ) {
			return;
		}

		$opts        = $this->get_options();
		$title       = $opts['meta_title'];
		$description = $opts['meta_description'];
		$og_image    = $opts['og_image_url'];
		$start_iso   = $opts['event_date_iso'];
		$end_iso     = $opts['event_end_iso'];
		$venue_name  = $opts['venue_name'];
		$venue_addr  = $opts['venue_address'];
		$org_name    = $opts['organizer_name'];
		$org_url     = $opts['organizer_url'];
		$page_url    = get_permalink();

		// Schema.org JSON-LD für BusinessEvent.
		// Adresse wird best-effort aus dem flachen `venue_address`-String geparst —
		// Settings-Page hält das aktuell als Single-Line-Feld; Schema.org will strukturiert.
		$address_struct = $this->parse_address( $venue_addr );

		$jsonld = array(
			'@context'            => 'https://schema.org',
			'@type'               => 'BusinessEvent',
			'name'                => $title,
			'description'         => $description,
			'startDate'           => $start_iso,
			'endDate'             => $end_iso,
			'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
			'eventStatus'         => 'https://schema.org/EventScheduled',
			'location'            => array(
				'@type'   => 'Place',
				'name'    => $venue_name,
				'address' => $address_struct,
			),
			'organizer'           => array(
				'@type' => 'Organization',
				'name'  => $org_name,
				'url'   => $org_url,
			),
			'image'               => $og_image ? array( $og_image ) : array(),
			'url'                 => $page_url,
			'inLanguage'          => 'en',
			'audience'            => array(
				'@type'        => 'BusinessAudience',
				'audienceType' => 'SAP SME Partners',
			),
		);

		echo "\n<!-- BEGIN: WBS SDC Bratislava SEO -->\n";
		printf(
			'<meta name="description" content="%s" />' . "\n",
			esc_attr( $description )
		);
		printf(
			'<link rel="canonical" href="%s" />' . "\n",
			esc_url( $page_url )
		);

		// Open Graph
		echo '<meta property="og:type" content="event" />' . "\n";
		printf(
			'<meta property="og:title" content="%s" />' . "\n",
			esc_attr( $title )
		);
		printf(
			'<meta property="og:description" content="%s" />' . "\n",
			esc_attr( $description )
		);
		printf(
			'<meta property="og:url" content="%s" />' . "\n",
			esc_url( $page_url )
		);
		echo '<meta property="og:locale" content="en_US" />' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "\n";
		if ( $og_image ) {
			printf(
				'<meta property="og:image" content="%s" />' . "\n",
				esc_url( $og_image )
			);
			echo '<meta property="og:image:width" content="1200" />' . "\n";
			echo '<meta property="og:image:height" content="630" />' . "\n";
			printf(
				'<meta property="og:image:alt" content="%s" />' . "\n",
				esc_attr( $title )
			);
		}
		printf(
			'<meta property="event:start_time" content="%s" />' . "\n",
			esc_attr( $start_iso )
		);
		printf(
			'<meta property="event:end_time" content="%s" />' . "\n",
			esc_attr( $end_iso )
		);

		// Twitter / X Card
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		printf(
			'<meta name="twitter:title" content="%s" />' . "\n",
			esc_attr( $title )
		);
		printf(
			'<meta name="twitter:description" content="%s" />' . "\n",
			esc_attr( $description )
		);
		if ( $og_image ) {
			printf(
				'<meta name="twitter:image" content="%s" />' . "\n",
				esc_url( $og_image )
			);
		}

		// Schema.org JSON-LD
		echo '<script type="application/ld+json">' . wp_json_encode(
			$jsonld,
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		) . '</script>' . "\n";

		echo "<!-- END: WBS SDC Bratislava SEO -->\n";
	}

	/**
	 * Best-effort-Adress-Parser für Schema.org `PostalAddress`.
	 *
	 * Das Settings-Feld `venue_address` ist eine Single-Line, z.B.
	 *   "Trnavská cesta 27/A, 831 04 Bratislava, Slovakia".
	 * Wir splitten an Kommata und mappen positional. Wenn das Format unbekannt
	 * ist, fallen wir auf einen Fallback mit nur `streetAddress` zurück.
	 */
	private function parse_address( $raw_address ) {
		$parts = array_map( 'trim', explode( ',', (string) $raw_address ) );
		$count = count( $parts );

		// Fallback: einzelner String.
		if ( $count < 2 ) {
			return array(
				'@type'         => 'PostalAddress',
				'streetAddress' => $raw_address,
			);
		}

		// Format-Annahme: "Street, ZIP City, Country".
		$street    = $parts[0];
		$zip_city  = $parts[1];
		$country   = $count >= 3 ? $parts[2] : '';

		// "831 04 Bratislava" → ZIP "831 04" + City "Bratislava".
		$postal_code = '';
		$locality    = $zip_city;
		if ( preg_match( '/^([\d\s]{4,8})\s+(.+)$/', $zip_city, $m ) ) {
			$postal_code = trim( $m[1] );
			$locality    = trim( $m[2] );
		}

		// Country-Code-Map (best-effort, default = volle Bezeichnung).
		$country_map = array(
			'slovakia'        => 'SK',
			'slowakei'        => 'SK',
			'germany'         => 'DE',
			'deutschland'     => 'DE',
			'austria'         => 'AT',
			'österreich'      => 'AT',
			'czech republic'  => 'CZ',
			'tschechien'      => 'CZ',
		);
		$country_key  = strtolower( $country );
		$country_code = $country_map[ $country_key ] ?? $country;

		return array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $street,
			'postalCode'      => $postal_code,
			'addressLocality' => $locality,
			'addressCountry'  => $country_code,
		);
	}

	/**
	 * Title-Override auf SDC-Pages.
	 */
	public function maybe_override_title( $title ) {
		if ( ! $this->is_sdc_page() ) {
			return $title;
		}
		$opts = $this->get_options();
		return $opts['meta_title'] ?: $title;
	}

	/**
	 * Yoast/RankMath-Description-Suppression auf SDC-Pages.
	 *
	 * Wir injizieren `<meta name="description">` selbst — Yoast soll seine eigene
	 * Description NICHT mehr ausgeben. `null` ist Yoast's Konvention für „skip output".
	 */
	public function maybe_suppress_yoast_desc( $value ) {
		return $this->is_sdc_page() ? null : $value;
	}

	public function maybe_suppress_yoast_og( $value ) {
		return $this->is_sdc_page() ? null : $value;
	}

	public function maybe_suppress_yoast_title( $value ) {
		return $this->is_sdc_page() ? null : $value;
	}
}
