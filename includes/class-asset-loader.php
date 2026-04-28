<?php
/**
 * Conditional Asset Loader (theme-agnostisch).
 *
 * REFACTOR Pass 4 Phase 2A: Loescht die Elementor-Hard-Dependency
 * (vorher Critical-Bug C-2). Detection laeuft jetzt via has_shortcode()
 * und has_block(), funktioniert in Twenty Twenty-Four, Astra, Avada,
 * Bricks, Divi, Beaver Builder, Block-Editor, Classic-Editor.
 *
 * Drei Layer:
 *   1) Pre-Detection (wp_enqueue_scripts) — Standardpfad
 *   2) Mark-Present (Shortcode-Render) — Catch-up bei dynamischen Renderern
 *   3) Footer-Late-Enqueue (wp_footer) — Last-Resort, wenn Pre-Detection ueber
 *      Custom-Render-Loops umgangen wurde
 *
 * Asset-Hashing: Vite produziert NICHT gehashte Filenames fuer den IIFE-Bundle
 * (Library-Mode = single chunk). Cache-Buster ist `WBS_SDC_VERSION` als
 * 3rd-Parameter zu wp_enqueue_*. Bei jedem Plugin-Update wird die Version
 * bumped → Browser laedt neu.
 *
 * Cache-Cocoon (gegen Autoptimize, WP-Rocket, LiteSpeed, W3TC):
 *   - data-no-optimize / data-no-defer / data-cfasync="false" via Tag-Filter
 *   - autoptimize_filter_js_exclude / _css_exclude
 *
 * Self-host Fonts (Phase 2B): Wir patchen den built CSS-Inhalt at runtime,
 * um den Google-Fonts-`@import` zu strippen, und prepend self-hosted
 * @font-face Deklarationen via Isolation::get_inline_css().
 *
 * @package WBS\SDC
 */

namespace WBS\SDC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Asset_Loader {

	const HANDLE_JS  = 'wbs-sdc-bratislava';
	const HANDLE_CSS = 'wbs-sdc-bratislava';

	/**
	 * Indicates that an SDC mount is on the current page.
	 *
	 * @var bool
	 */
	private static $force_enqueue = false;

	public function __construct() {
		// Layer 1: Pre-Detection auf Frontend-Render.
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_preenqueue' ), 10 );

		// Layer 3: Catch-up im Footer (sehr selten).
		add_action( 'wp_footer', array( $this, 'maybe_lateenqueue' ), 1 );

		// Editor-Preview (Elementor + Block-Editor).
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_for_editor' ) );
		add_action( 'elementor/preview/enqueue_scripts',      array( $this, 'enqueue_for_editor' ) );

		// Cache-Cocoon-Filter werden global registriert (greifen nur fuer unseren Handle).
		$this->register_cache_cocoon_filters();
	}

	/**
	 * Layer 2: Wird vom Shortcode aufgerufen, wenn der Render tatsaechlich passiert ist.
	 * Forciert das Enqueue, falls Pre-Detection in Layer 1 fehlgeschlagen war.
	 */
	public static function mark_present() {
		self::$force_enqueue = true;
		// Wenn wir bereits in der wp_enqueue_scripts-Phase ODER danach sind, greifen wir direkt.
		if ( ! wp_script_is( self::HANDLE_JS, 'enqueued' ) && ! wp_script_is( self::HANDLE_JS, 'done' ) ) {
			self::enqueue();
		}
	}

	/**
	 * Layer 1: Pre-Detection.
	 */
	public function maybe_preenqueue() {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( ! $post || empty( $post->post_content ) ) {
			return;
		}

		$has_shortcode = has_shortcode( $post->post_content, Shortcode::TAG );
		$has_block     = function_exists( 'has_block' )
			? has_block( Shortcode::BLOCK_ID, $post )
			: false;

		if ( $has_shortcode || $has_block ) {
			self::enqueue();
		}
	}

	/**
	 * Layer 3: Footer-Late-Enqueue Catch-Up.
	 */
	public function maybe_lateenqueue() {
		if ( self::$force_enqueue && ! wp_script_is( self::HANDLE_JS, 'enqueued' ) ) {
			self::enqueue();
		}
	}

	/**
	 * Editor-Preview: immer enqueuen (Elementor-iframe).
	 */
	public function enqueue_for_editor() {
		self::enqueue();
	}

	/**
	 * Eigentliches Enqueue von JS + CSS.
	 *
	 * - JS: Footer, kein dependency-tree (self-contained IIFE)
	 * - CSS: Patcht Google-Fonts-@import out, prepended self-hosted @font-face
	 */
	public static function enqueue() {
		$css_src = WBS_SDC_BUILD_URL . 'sdc-bratislava.css';
		$js_src  = WBS_SDC_BUILD_URL . 'sdc-bratislava.iife.js';
		$css_dir = WBS_SDC_BUILD_DIR . 'sdc-bratislava.css';

		// Self-host fonts: Strip Google Fonts @import + prepend self-hosted @font-face.
		// Wir registrieren ein "leeres" Stylesheet und lassen die gesamte CSS via inline laden,
		// um den @import zur Laufzeit zu strippen ohne Source-Edit am React-Bundle.
		if ( file_exists( $css_dir ) ) {
			$css_content = (string) file_get_contents( $css_dir );
			$css_content = self::strip_google_fonts_import( $css_content );

			wp_register_style(
				self::HANDLE_CSS,
				false, // Marker-only, no src
				array(),
				WBS_SDC_VERSION
			);
			wp_enqueue_style( self::HANDLE_CSS );

			// Inline-Order: 1) Self-hosted Font-Face Deklarationen, 2) Theme-Isolation Reset,
			// 3) Bundle-CSS (mit gestripptem Google-Fonts-@import).
			wp_add_inline_style( self::HANDLE_CSS, Isolation::get_inline_css() );
			wp_add_inline_style( self::HANDLE_CSS, $css_content );
		} else {
			// Fallback: build/ fehlt (Dev-Build). Wir registrieren trotzdem, damit Plugin nicht crashed.
			wp_register_style(
				self::HANDLE_CSS,
				$css_src,
				array(),
				WBS_SDC_VERSION
			);
			wp_enqueue_style( self::HANDLE_CSS );
			wp_add_inline_style( self::HANDLE_CSS, Isolation::get_inline_css() );
		}

		// JS in Footer (kein FOUC, weil CSS bereits im Head).
		wp_enqueue_script(
			self::HANDLE_JS,
			$js_src,
			array(),
			WBS_SDC_VERSION,
			true
		);
	}

	/**
	 * Strippt @import url(https://fonts.googleapis.com/...) aus dem built CSS,
	 * damit kein User-IP-Leak nach Google US passiert (GDPR-Compliance).
	 *
	 * @param string $css Roh-CSS.
	 * @return string CSS ohne Google-Fonts-Imports.
	 */
	private static function strip_google_fonts_import( $css ) {
		return preg_replace(
			'#@import\s+url\(\s*[\'"]?https?://fonts\.(?:googleapis|gstatic)\.com[^)]*\)\s*;?#i',
			'',
			$css
		);
	}

	/**
	 * Cache-Cocoon: data-Attribute via Tag-Filter +
	 * Autoptimize-/LiteSpeed-Excludes.
	 */
	private function register_cache_cocoon_filters() {
		// Autoptimize JS-Exclude.
		add_filter( 'autoptimize_filter_js_exclude', function ( $exclude ) {
			$exclude = (string) $exclude;
			if ( strpos( $exclude, 'sdc-bratislava' ) === false ) {
				$exclude .= ', sdc-bratislava.iife.js';
			}
			return $exclude;
		} );

		// Autoptimize CSS-Exclude.
		add_filter( 'autoptimize_filter_css_exclude', function ( $exclude ) {
			$exclude = (string) $exclude;
			if ( strpos( $exclude, 'sdc-bratislava' ) === false ) {
				$exclude .= ', sdc-bratislava.css';
			}
			return $exclude;
		} );

		// Tag-Filter fuer <script>.
		add_filter( 'script_loader_tag', array( $this, 'add_no_optimize_to_script' ), 10, 2 );

		// Tag-Filter fuer <link rel="stylesheet">.
		add_filter( 'style_loader_tag', array( $this, 'add_no_optimize_to_style' ), 10, 2 );
	}

	/**
	 * @param string $tag    Original-Tag.
	 * @param string $handle Script-Handle.
	 * @return string Modifizierter Tag.
	 */
	public function add_no_optimize_to_script( $tag, $handle ) {
		if ( $handle !== self::HANDLE_JS ) {
			return $tag;
		}
		// data-no-optimize       → Autoptimize, LiteSpeed Cache
		// data-no-defer          → WP-Rocket / Autoptimize "do not defer"
		// data-cfasync="false"   → Cloudflare Rocket Loader skip
		return str_replace(
			'<script ',
			'<script data-no-optimize="1" data-no-defer="1" data-cfasync="false" ',
			$tag
		);
	}

	/**
	 * @param string $tag    Original-Tag.
	 * @param string $handle Style-Handle.
	 * @return string Modifizierter Tag.
	 */
	public function add_no_optimize_to_style( $tag, $handle ) {
		if ( $handle !== self::HANDLE_CSS ) {
			return $tag;
		}
		return str_replace(
			'<link ',
			'<link data-no-optimize="1" data-cfasync="false" ',
			$tag
		);
	}
}
