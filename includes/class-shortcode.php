<?php
/**
 * Shortcode `[sdc_bratislava]`.
 *
 * Primaerer Mount-Mechanismus fuer die Landing-Page.
 * Renderet einen idempotenten Mount-Wrapper plus inline init-script.
 * Asset-Enqueue wird via Asset_Loader::mark_present() forciert
 * (Catch-up falls Pre-Detection auf wp_enqueue_scripts fehlgeschlagen).
 *
 * @package WBS\SDC
 */

namespace WBS\SDC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcode {

	const TAG       = 'sdc_bratislava';
	const MOUNT_ID  = 'sdcb-root';
	const BLOCK_ID  = 'wbs-sdc/bratislava-2026';

	public function __construct() {
		add_shortcode( self::TAG, array( $this, 'render' ) );

		// WP "smart-quotes"-Filter darf den Shortcode-Output NICHT anfassen.
		add_filter( 'no_texturize_shortcodes', function ( $list ) {
			$list[] = self::TAG;
			return $list;
		} );
	}

	/**
	 * Render-Callback.
	 *
	 * @param array       $atts    Shortcode-Attribute (Phase 2C: Settings-Page-overrides).
	 * @param string|null $content Shortcode-Content (ungenutzt).
	 * @param string      $tag     Shortcode-Tag.
	 * @return string Rendered HTML.
	 */
	public function render( $atts = array(), $content = null, $tag = '' ) {
		// Idempotenz: Doppel-Mount auf derselben Page abfangen.
		static $rendered = false;
		if ( $rendered ) {
			return '<!-- [sdc_bratislava] already rendered on this page -->';
		}
		$rendered = true;

		// Asset-Loader signalisieren, dass Shortcode auf der Page ist.
		// Greift fuer Faelle, in denen has_shortcode()-Pre-Detection fehlschlug
		// (z.B. dynamic-render Themes, Page-Builder die do_shortcode() in Custom-Loops aufrufen).
		Asset_Loader::mark_present();

		$mount_id = self::MOUNT_ID;
		$version  = WBS_SDC_VERSION;

		ob_start();
		?>
		<div class="wbs-sdc-isolation-wrapper sdc-isolation alignfull" data-version="<?php echo esc_attr( $version ); ?>">
			<div id="<?php echo esc_attr( $mount_id ); ?>" class="wbs-sdc-mount"></div>
			<script data-no-optimize="1" data-cfasync="false">
				(function () {
					var sel = '#<?php echo esc_js( $mount_id ); ?>';
					function go() {
						if (window.SDCBratislava && typeof window.SDCBratislava.mount === 'function') {
							window.SDCBratislava.mount(sel);
						}
					}
					if (window.SDCBratislava) { go(); }
					else { document.addEventListener('sdc-bratislava:ready', go); }
				})();
			</script>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
