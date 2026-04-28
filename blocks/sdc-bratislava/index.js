/**
 * SDC Bratislava 2026 — Gutenberg Block (Editor-Side).
 *
 * Renderet einen statischen Placeholder im Block-Editor — der eigentliche
 * 312-KB-React-Bundle wuerde den Editor verlangsamen, daher hier nur eine
 * dezente Vorschau. Frontend-Render erfolgt server-seitig via render.php
 * (Delegation an [sdc_bratislava] Shortcode).
 *
 * @see render.php
 */
( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.blockEditor || ! wp.element ) {
		return;
	}

	var registerBlockType = wp.blocks.registerBlockType;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var createElement = wp.element.createElement;
	var __ = ( wp.i18n && wp.i18n.__ ) ? wp.i18n.__ : function ( s ) { return s; };

	registerBlockType( 'wbs-sdc/bratislava-2026', {
		edit: function () {
			var blockProps = useBlockProps( {
				style: {
					border: '2px dashed #A64D79',
					padding: '2rem',
					textAlign: 'center',
					background: '#F5ECF1',
					color: '#1A0F1F',
					fontFamily: 'system-ui, sans-serif',
					borderRadius: '8px',
				},
			} );

			return createElement(
				'div',
				blockProps,
				createElement( 'strong', null, 'SDC Bratislava 2026' ),
				createElement(
					'p',
					{ style: { marginTop: '0.5rem', fontSize: '0.875rem' } },
					__(
						'Die komplette Landingpage wird im Frontend gerendert.'
						+ ' Hier zum Schutz des Editors nur ein Placeholder.',
						'wbs-sdc-bratislava'
					)
				)
			);
		},
		// Server-side rendering — save() liefert null.
		save: function () {
			return null;
		},
	} );
} )( window.wp );
