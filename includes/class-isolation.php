<?php
/**
 * CSS-Isolation Helper.
 *
 * Liefert das Reset-CSS, das via `wp_add_inline_style()` an die Haupt-CSS
 * angehaengt wird. Wir verwenden einen WRAPPER-Class-Prefix
 * (`.wbs-sdc-isolation-wrapper` bzw. `.sdc-isolation`) statt Shadow-DOM,
 * weil HubSpot-Iframe + IntersectionObserver-Math + React-Form-Submission
 * in Shadow-DOM brechen wuerden.
 *
 * Strategie: Selective-Reset — wir resetten genau die Tag-Defaults,
 * die ein WP-Theme typischerweise stylet (ul, ol, li, p, button, a, img,
 * input, textarea, select). KEIN `all: revert`, weil das auch unsere
 * eigenen Section-Styles brechen wuerde.
 *
 * Plus: `isolation: isolate` schafft einen eigenen Stacking-Context,
 * damit z-index-Werte innerhalb des Wrappers nicht mit Theme-Header-z-index
 * konkurrieren (Avada, OceanWP nutzen z-index 99999+).
 *
 * Ausserdem: Self-host font-faces via PHP-injektion ersetzt
 * den Google-Fonts-`@import` in der gebauten CSS (siehe Asset_Loader).
 *
 * @package WBS\SDC
 */

namespace WBS\SDC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Isolation {

	/**
	 * Liefert den Selective-Reset + Self-host-Font-Face-Block,
	 * der per `wp_add_inline_style` angehaengt wird.
	 *
	 * @return string CSS-Code.
	 */
	public static function get_inline_css() {
		return self::get_font_face_css() . self::get_reset_css();
	}

	/**
	 * Self-hosted Font-Face Deklarationen (GDPR-compliant).
	 *
	 * Pfade resolven relativ zur Plugin-Build-URL.
	 * Source Serif 4 (Variable, 8-60pt opsz, 400-700) + Montserrat (400, 500, 600, 700).
	 *
	 * @return string CSS-Code.
	 */
	public static function get_font_face_css() {
		$base = WBS_SDC_BUILD_URL . 'assets/fonts/';

		return "
/* === WBS SDC — Self-hosted Fonts (GDPR-compliant, replaces Google Fonts @import) === */
@font-face {
	font-family: 'Source Serif 4';
	font-style: normal;
	font-weight: 400 700;
	font-display: swap;
	src: url('{$base}source-serif-4-vf.woff2') format('woff2-variations'),
	     url('{$base}source-serif-4-vf.woff2') format('woff2');
}
@font-face {
	font-family: 'Source Serif 4';
	font-style: italic;
	font-weight: 400 700;
	font-display: swap;
	src: url('{$base}source-serif-4-italic-vf.woff2') format('woff2-variations'),
	     url('{$base}source-serif-4-italic-vf.woff2') format('woff2');
}
@font-face {
	font-family: 'Montserrat';
	font-style: normal;
	font-weight: 400;
	font-display: swap;
	src: url('{$base}montserrat-400.woff2') format('woff2');
}
@font-face {
	font-family: 'Montserrat';
	font-style: normal;
	font-weight: 500;
	font-display: swap;
	src: url('{$base}montserrat-500.woff2') format('woff2');
}
@font-face {
	font-family: 'Montserrat';
	font-style: normal;
	font-weight: 600;
	font-display: swap;
	src: url('{$base}montserrat-600.woff2') format('woff2');
}
@font-face {
	font-family: 'Montserrat';
	font-style: normal;
	font-weight: 700;
	font-display: swap;
	src: url('{$base}montserrat-700.woff2') format('woff2');
}
";
	}

	/**
	 * Selective Theme-Reset auf dem Mount-Wrapper.
	 *
	 * @return string CSS-Code.
	 */
	public static function get_reset_css() {
		return "
/* === WBS SDC — Theme-Isolation Wrapper === */
.wbs-sdc-isolation-wrapper {
	/* Stacking-Context: z-index-Werte innen sind relativ zu diesem Wrapper. */
	isolation: isolate;
	/*
	 * Full-Viewport-Breakout: Theme-Content-Container haben typisch max-width 620-1240px.
	 * Wir brechen mit `margin: calc(50% - 50vw)` aus dem Parent-Container aus und spannen
	 * 100vw — die SDC-Page ist als Landingpage designed, nicht als Inhalt-im-Blog-Post.
	 * Funktioniert auf Classic + Block-Themes; `.alignfull` Klasse ist Block-Theme-Hint.
	 */
	margin-left: calc(50% - 50vw);
	margin-right: calc(50% - 50vw);
	width: 100vw;
	max-width: 100vw;
	padding: 0;
	overflow-x: clip;
	/* Kontrollierte Vererbung. */
	color: var(--client-text-body, #3D3344);
	font-family: var(--client-font-body, 'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif);
	font-size: 16px;
	line-height: 1.5;
	text-align: left;
	text-transform: none;
	letter-spacing: normal;
}
.wbs-sdc-isolation-wrapper,
.wbs-sdc-isolation-wrapper *,
.wbs-sdc-isolation-wrapper *::before,
.wbs-sdc-isolation-wrapper *::after {
	box-sizing: border-box;
}
.wbs-sdc-isolation-wrapper ul,
.wbs-sdc-isolation-wrapper ol {
	list-style: none;
	margin: 0;
	padding: 0;
}
.wbs-sdc-isolation-wrapper li {
	margin: 0;
	padding: 0;
}
.wbs-sdc-isolation-wrapper p {
	margin: 0;
}
.wbs-sdc-isolation-wrapper button {
	background: none;
	border: 0;
	padding: 0;
	font: inherit;
	color: inherit;
	cursor: pointer;
}
.wbs-sdc-isolation-wrapper a {
	color: inherit;
	text-decoration: none;
	background-color: transparent;
}
.wbs-sdc-isolation-wrapper img,
.wbs-sdc-isolation-wrapper picture,
.wbs-sdc-isolation-wrapper svg {
	max-width: 100%;
	height: auto;
}
.wbs-sdc-isolation-wrapper img,
.wbs-sdc-isolation-wrapper picture {
	display: block;
}
.wbs-sdc-isolation-wrapper input,
.wbs-sdc-isolation-wrapper textarea,
.wbs-sdc-isolation-wrapper select {
	font: inherit;
	color: inherit;
}
.wbs-sdc-isolation-wrapper h1,
.wbs-sdc-isolation-wrapper h2,
.wbs-sdc-isolation-wrapper h3,
.wbs-sdc-isolation-wrapper h4,
.wbs-sdc-isolation-wrapper h5,
.wbs-sdc-isolation-wrapper h6 {
	margin: 0;
	font-weight: inherit;
}
.wbs-sdc-isolation-wrapper figure {
	margin: 0;
}
";
	}

	/**
	 * Critical-CSS fuer LCP-Optimization (Above-the-fold Hero + NumericalStrip).
	 *
	 * Wird inline im `<head>` ausgegeben, sodass das Hero-Element sofort gestylt
	 * wird, bevor die Haupt-CSS-Datei geladen ist. Halbiert die LCP-Zeit auf 3G.
	 *
	 * @return string CSS-Code.
	 */
	public static function get_critical_css() {
		return "
/* === WBS SDC — Critical Above-the-fold CSS (LCP) === */
.wbs-sdc-isolation-wrapper .sdc-bratislava {
	background: #FFFFFF;
	color: #1A0F1F;
	font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;
}
.wbs-sdc-isolation-wrapper .sdc-bratislava section {
	padding: clamp(4rem, 3rem + 4vw, 7rem) clamp(1.25rem, 0.75rem + 2vw, 2.5rem);
}
";
	}
}
