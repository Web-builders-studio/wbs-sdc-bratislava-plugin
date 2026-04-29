<?php
/**
 * Settings_Page — WP-Admin → Einstellungen → SDC Bratislava.
 *
 * Lightweight Settings-Page nur für SEO-/Event-Metadata-Felder (Title,
 * Description, OG-Image, Event-Date Start/End, Venue, Organizer). Page-Content
 * selbst ist im React-Bundle hartkodiert und wird via Plugin-Update ausgerollt.
 *
 * Verwendet vanilla WP-Settings-API (kein Custom-Form-Handling).
 * Capability: `manage_options` (Admin only).
 * CSRF-Schutz: automatisch via `settings_fields()`.
 *
 * Spec: 14-WP-PLUGIN-REVIEW.md §C.2
 *
 * @package WBS\SDC
 */

namespace WBS\SDC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings_Page {

	const PAGE_SLUG  = 'wbs-sdc-bratislava';
	const SECTION_ID = 'wbs_sdc_seo_section';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Menü-Eintrag unter „Einstellungen".
	 */
	public function register_menu() {
		add_options_page(
			__( 'SDC Bratislava 2026', 'wbs-sdc-bratislava' ),
			__( 'SDC Bratislava', 'wbs-sdc-bratislava' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Settings-Section + Felder registrieren.
	 */
	public function register_settings() {
		register_setting( self::PAGE_SLUG, WBS_SDC_OPTION_KEY, array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize' ),
			'default'           => array(),
		) );

		add_settings_section(
			self::SECTION_ID,
			__( 'SEO & Event Metadata', 'wbs-sdc-bratislava' ),
			array( $this, 'render_section_intro' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'meta_title',
			__( 'Meta-Title', 'wbs-sdc-bratislava' ),
			array( $this, 'render_field_meta_title' ),
			self::PAGE_SLUG,
			self::SECTION_ID
		);

		add_settings_field(
			'meta_description',
			__( 'Meta-Description', 'wbs-sdc-bratislava' ),
			array( $this, 'render_field_meta_description' ),
			self::PAGE_SLUG,
			self::SECTION_ID
		);

		add_settings_field(
			'og_image_url',
			__( 'OG-Image URL (1200×630)', 'wbs-sdc-bratislava' ),
			array( $this, 'render_field_og_image_url' ),
			self::PAGE_SLUG,
			self::SECTION_ID
		);

		add_settings_field(
			'event_date_iso',
			__( 'Event-Start (ISO 8601)', 'wbs-sdc-bratislava' ),
			array( $this, 'render_field_event_date_iso' ),
			self::PAGE_SLUG,
			self::SECTION_ID
		);

		add_settings_field(
			'event_end_iso',
			__( 'Event-Ende (ISO 8601)', 'wbs-sdc-bratislava' ),
			array( $this, 'render_field_event_end_iso' ),
			self::PAGE_SLUG,
			self::SECTION_ID
		);
	}

	public function render_section_intro() {
		echo '<p>' . esc_html__(
			'Diese Felder werden im <head>-Bereich der SDC-Page injected (Meta-Tags + JSON-LD Event-Schema). Inhalte der Page selbst sind im React-Bundle hartkodiert und werden über Plugin-Updates ausgerollt.',
			'wbs-sdc-bratislava'
		) . '</p>';
	}

	private function get_value( $key ) {
		$opts = get_option( WBS_SDC_OPTION_KEY, array() );
		return is_array( $opts ) && isset( $opts[ $key ] ) ? $opts[ $key ] : '';
	}

	public function render_field_meta_title() {
		printf(
			'<input type="text" id="wbs-sdc-meta-title" name="%1$s[meta_title]" value="%2$s" class="regular-text" maxlength="120" />',
			esc_attr( WBS_SDC_OPTION_KEY ),
			esc_attr( $this->get_value( 'meta_title' ) )
		);
		echo '<p class="description">' . esc_html__( 'Empfohlen: 50–60 Zeichen. Wird auch als Twitter-Card-Titel verwendet.', 'wbs-sdc-bratislava' ) . '</p>';
	}

	public function render_field_meta_description() {
		printf(
			'<textarea id="wbs-sdc-meta-description" name="%1$s[meta_description]" rows="3" class="large-text" maxlength="320">%2$s</textarea>',
			esc_attr( WBS_SDC_OPTION_KEY ),
			esc_textarea( $this->get_value( 'meta_description' ) )
		);
		echo '<p class="description">' . esc_html__( 'Empfohlen: 140–160 Zeichen. Wird in Google-Snippets, OG-Cards und JSON-LD verwendet.', 'wbs-sdc-bratislava' ) . '</p>';
	}

	public function render_field_og_image_url() {
		printf(
			'<input type="url" id="wbs-sdc-og-image" name="%1$s[og_image_url]" value="%2$s" class="regular-text" placeholder="https://…/og-image-1200x630.png" />',
			esc_attr( WBS_SDC_OPTION_KEY ),
			esc_url( $this->get_value( 'og_image_url' ) )
		);
		echo '<p class="description">' . esc_html__( 'Format: 1200×630 px (LinkedIn/Slack/X), JPG/PNG/WebP. Leer = kein OG-Image.', 'wbs-sdc-bratislava' ) . '</p>';
	}

	public function render_field_event_date_iso() {
		printf(
			'<input type="text" id="wbs-sdc-event-start" name="%1$s[event_date_iso]" value="%2$s" class="regular-text code" placeholder="2026-06-10T08:00:00+02:00" />',
			esc_attr( WBS_SDC_OPTION_KEY ),
			esc_attr( $this->get_value( 'event_date_iso' ) )
		);
		echo '<p class="description">' . esc_html__( 'ISO-8601 mit Timezone-Offset. Beispiel: 2026-06-10T08:00:00+02:00.', 'wbs-sdc-bratislava' ) . '</p>';
	}

	public function render_field_event_end_iso() {
		printf(
			'<input type="text" id="wbs-sdc-event-end" name="%1$s[event_end_iso]" value="%2$s" class="regular-text code" placeholder="2026-06-11T18:00:00+02:00" />',
			esc_attr( WBS_SDC_OPTION_KEY ),
			esc_attr( $this->get_value( 'event_end_iso' ) )
		);
	}

	/**
	 * Sanitize-Callback (WP-Settings-API).
	 *
	 *   - Capability-Recheck (Defense-in-Depth — `register_setting` macht das auch,
	 *     aber doppelt-genäht hält besser).
	 *   - User-editierbare Felder werden gefiltert + escaped.
	 *   - Nicht-User-editierbare Felder (venue_*, organizer_*) bleiben unverändert
	 *     auf den Default-Werten aus Activation.
	 *   - ISO-8601-Validierung mit `strtotime`-Fallback. Bei Invalid: alten Wert
	 *     wiederherstellen + `add_settings_error()` für UI-Feedback.
	 */
	public function sanitize( $input ) {
		if ( ! is_array( $input ) ) {
			return get_option( WBS_SDC_OPTION_KEY, array() );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return get_option( WBS_SDC_OPTION_KEY, array() );
		}

		$existing = get_option( WBS_SDC_OPTION_KEY, array() );
		$existing = is_array( $existing ) ? $existing : array();

		$clean = array();
		$clean['meta_title']       = sanitize_text_field( $input['meta_title']       ?? ( $existing['meta_title']       ?? '' ) );
		$clean['meta_description'] = sanitize_textarea_field( $input['meta_description'] ?? ( $existing['meta_description'] ?? '' ) );
		$clean['og_image_url']     = esc_url_raw( $input['og_image_url']             ?? ( $existing['og_image_url']     ?? '' ) );
		$clean['event_date_iso']   = sanitize_text_field( $input['event_date_iso']   ?? ( $existing['event_date_iso']   ?? '' ) );
		$clean['event_end_iso']    = sanitize_text_field( $input['event_end_iso']    ?? ( $existing['event_end_iso']    ?? '' ) );

		// ISO-8601-Validierung.
		foreach ( array( 'event_date_iso', 'event_end_iso' ) as $key ) {
			if ( '' !== $clean[ $key ] && false === strtotime( $clean[ $key ] ) ) {
				add_settings_error(
					WBS_SDC_OPTION_KEY,
					"invalid_$key",
					sprintf(
						/* translators: %s: Feldname (event_date_iso oder event_end_iso) */
						esc_html__( '%s ist kein gültiges ISO-8601-Datum. Vorheriger Wert wurde beibehalten.', 'wbs-sdc-bratislava' ),
						esc_html( $key )
					)
				);
				$clean[ $key ] = $existing[ $key ] ?? '';
			}
		}

		// Cross-Field-Validierung: End >= Start.
		if (
			! empty( $clean['event_date_iso'] ) && ! empty( $clean['event_end_iso'] )
			&& strtotime( $clean['event_end_iso'] ) < strtotime( $clean['event_date_iso'] )
		) {
			add_settings_error(
				WBS_SDC_OPTION_KEY,
				'event_end_before_start',
				esc_html__( 'Event-Ende liegt vor Event-Start. Vorheriger Wert wurde beibehalten.', 'wbs-sdc-bratislava' )
			);
			$clean['event_end_iso'] = $existing['event_end_iso'] ?? '';
		}

		// Nicht-User-editierbare Felder behalten ihre Werte aus Activation.
		$clean['venue_name']     = $existing['venue_name']     ?? 'DoubleTree by Hilton Bratislava';
		$clean['venue_address']  = $existing['venue_address']  ?? 'Trnavská cesta 27/A, 831 04 Bratislava, Slovakia';
		$clean['organizer_name'] = $existing['organizer_name'] ?? 'LinkedWorld AG';
		$clean['organizer_url']  = $existing['organizer_url']  ?? 'https://www.linkedworld.eu/';

		return $clean;
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unzureichende Berechtigungen.', 'wbs-sdc-bratislava' ) );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'SDC Bratislava 2026', 'wbs-sdc-bratislava' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Plugin-Version:', 'wbs-sdc-bratislava' ); ?>
				<code><?php echo esc_html( WBS_SDC_VERSION ); ?></code>
			</p>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::PAGE_SLUG );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
