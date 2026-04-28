<?php
/**
 * Plugin Bootstrap (Singleton).
 *
 * Verbindet Asset-Loader, Shortcode, Block und Cache-Cocoon.
 * Bei der Konstruktion werden alle Hooks registriert. KEINE eigene Logik
 * im Konstruktor — Sub-Klassen tragen die Verantwortung.
 *
 * @package WBS\SDC
 */

namespace WBS\SDC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	/**
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * @var Asset_Loader
	 */
	public $asset_loader;

	/**
	 * @var Shortcode
	 */
	public $shortcode;

	/**
	 * Singleton-Accessor.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private Konstruktor — instanziert alle Sub-Module + registriert globale Hooks.
	 */
	private function __construct() {
		$this->asset_loader = new Asset_Loader();
		$this->shortcode    = new Shortcode();

		// Block-Registration laeuft auf `init`-Hook (von Block::register intern angemeldet).
		add_action( 'init', array( Block::class, 'register' ) );

		// Cache-Cocoon: Cache-Control-Header fuer SDC-Pages
		// (verhindert dass HubSpot-iframe-State zwischen Usern gecached wird).
		add_action( 'send_headers', array( $this, 'send_cache_cocoon_headers' ), 10 );
	}

	/**
	 * Cache-Cocoon: HTTP-Header fuer Pages mit SDC-Shortcode/Block.
	 *
	 * HubSpot-iframe-State (Form-Submission, A/B-Tests) darf NICHT zwischen
	 * Usern via Shared-Cache (CDN, Reverse-Proxy) gecached werden. Browser-Cache
	 * 5 min ist OK fuer Repeat-Visitors.
	 *
	 * Wird nur ausgeloest, wenn Page-Content den Shortcode/Block enthaelt.
	 */
	public function send_cache_cocoon_headers() {
		if ( is_admin() || ! is_singular() ) {
			return;
		}
		if ( headers_sent() ) {
			return;
		}

		$post = get_queried_object();
		if ( ! $post || empty( $post->post_content ) ) {
			return;
		}

		$has_shortcode = has_shortcode( $post->post_content, Shortcode::TAG );
		$has_block     = has_block( 'wbs-sdc/bratislava-2026', $post );

		if ( ! $has_shortcode && ! $has_block ) {
			return;
		}

		// Browser darf 5 min cachen, Shared-Cache (CDN/Proxy) darf NICHT cachen.
		header( 'Cache-Control: public, max-age=300, s-maxage=0' );
		header( 'Vary: Accept-Encoding, Cookie' );
	}

	/**
	 * Singleton — clone und unserialize verbieten.
	 */
	private function __clone() {}
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}
}
