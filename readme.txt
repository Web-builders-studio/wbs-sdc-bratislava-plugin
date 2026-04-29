=== WBS — SDC Bratislava 2026 ===
Contributors: webbuildersstudio
Tags: event, landingpage, linkedworld, sap, gutenberg-block
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Theme-agnostische Event-Landingpage für SME Solution Day Connect 2026 (LinkedWorld AG, DoubleTree by Hilton Bratislava, June 10–11).

== Description ==

Stellt einen Shortcode `[sdc_bratislava]`, einen Gutenberg-Block und ein optionales Elementor-Widget bereit, die die komplette SDC-Bratislava-2026-Landingpage in eine bestehende WordPress-Seite einbetten. Self-contained React-Bundle, keine externen Dependencies. CSS-Isolation gegen Theme-Bleed. GDPR-konformes Self-hosted-Font-Loading. Schema.org BusinessEvent JSON-LD im `<head>`. Auto-Updates via GitHub-Release.

= Features =
* `[sdc_bratislava]` Shortcode (primärer Mount-Mechanismus)
* Gutenberg-Block `wbs-sdc/bratislava-2026` (Block-API v3)
* Optional: Elementor-Widget (lädt nur wenn Elementor aktiv)
* CSS-Isolation-Wrapper verhindert Theme-CSS-Bleed
* SEO-Injector: Meta + Open Graph + Twitter-Card + Schema.org BusinessEvent JSON-LD
* Settings-Page (Einstellungen → SDC Bratislava) für Meta-Title, Description, OG-Image, Event-Datum
* GDPR: Google-Fonts `@import`-Strip zur Laufzeit, Self-hosted-Fonts im Bundle
* Cache-Cocoon für WP Rocket / Autoptimize / LiteSpeed (verhindert Re-Minification des IIFE-Bundles)
* Auto-Update via Plugin-Update-Checker (YahnisElsts v5.6) + GitHub-Releases

Author: web-builders.studio (https://web-builders.studio)

== Installation ==

1. ZIP unter Plugins → Hochladen → Plugin hochladen → Datei auswählen → Jetzt installieren.
2. Plugin aktivieren.
3. Eine beliebige Page öffnen und entweder:
   * Shortcode `[sdc_bratislava]` einfügen, oder
   * Gutenberg-Block „WBS — Solution Day Connect" platzieren, oder
   * (falls Elementor aktiv) Widget „WBS — Solution Day Connect" platzieren.
4. Optional: Einstellungen → SDC Bratislava → SEO-Felder befüllen (Meta-Title, OG-Image, Event-Datum).

== Frequently Asked Questions ==

= Funktioniert das Plugin ohne Elementor? =

Ja. Shortcode und Gutenberg-Block sind die primären Mount-Mechanismen. Elementor wird nur optional verwendet, wenn es aktiv ist.

= Welches Theme wird unterstützt? =

Theme-agnostisch. Getestet wurde mit Twenty Twenty-Four, Astra und Avada. Die Page wird in einen Isolation-Wrapper gerendert, der gegen Theme-CSS-Bleed schützt.

= Werden Cookies ohne Consent gesetzt? =

Self-hosted-Fonts laden ohne externe Anfragen. Die HubSpot-Form (Lead-Capture in der Final-CTA-Section) lädt erst bei Sichtbarkeit ihren Script — Consent-Manager-Integration ist Aufgabe des Customer-Setups (Borlabs/Cookiebot/Complianz).

= Wie funktionieren Auto-Updates? =

Plugin-Update-Checker prüft `web-builders-studio/wbs-sdc-bratislava-plugin` GitHub-Releases. Bei einer neuen Release-Tag-Version erscheint im WP-Admin der gewohnte Update-Hinweis.

== Changelog ==

= 1.0.1 =
* Fix: Full-Viewport-Breakout im Isolation-Wrapper — Theme-Content-Container (max-width 620–1240px) kollabierte die Landing-Page auf Mobile-Layout. Wrapper sprengt jetzt mit `margin: calc(50% - 50vw); width: 100vw` aus dem Theme-Container aus.
* Fix: `alignfull` Klasse am Wrapper als Hint für Block-Themes (Twenty Twenty-Five etc.).

= 1.0.0 =
* Initial release.
* Theme-agnostische Architektur (Shortcode + Block + optionale Elementor-Bridge).
* SEO-Hooks (Meta + OG + Twitter + Schema.org BusinessEvent JSON-LD).
* Settings-Page für SEO-Metadata.
* CSS-Isolation gegen Theme-Bleed.
* GDPR: Google-Fonts-Strip + Self-hosted-Fonts.
* Cache-Cocoon für gängige Cache-Plugins.
* Plugin-Update-Checker (YahnisElsts v5.6) für GitHub-Release-basierte Auto-Updates.
