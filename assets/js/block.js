/**
 * The DiceBar block.
 *
 * Plain JavaScript against the wp.* globals rather than JSX, so there is no
 * build step and nothing compiled that could fall out of step with this file.
 *
 * The bar is drawn on the server, by the same code as the shortcode, and the
 * editor shows it through ServerSideRender. That costs a request per change
 * but means the block in the editor is the bar itself rather than a drawing of
 * it, so it cannot look right here and wrong on the site.
 */
( function ( blocks, element, blockEditor, components, i18n, serverSideRender ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;
	var ServerSideRender = serverSideRender;

	blocks.registerBlockType( 'dicebar/bar', {
		edit: function ( props ) {
			var items = props.attributes.items || '';

			var inspector = el(
				blockEditor.InspectorControls,
				null,
				el(
					components.PanelBody,
					{ title: __( 'Which items', 'dicebar' ), initialOpen: true },
					el( components.TextControl, {
						label: __( 'Item IDs', 'dicebar' ),
						help: __(
							'Leave empty for every item. To show only some, list their IDs separated by commas. Each item on the DiceBar settings screen shows its own ID.',
							'dicebar'
						),
						value: items,
						onChange: function ( next ) {
							props.setAttributes( { items: next } );
						},
					} )
				)
			);

			var body = el( ServerSideRender, {
				block: 'dicebar/bar',
				attributes: { items: items },
				// Shown while the server answers, and whenever it answers with
				// nothing: an empty bar means no items are configured yet, and
				// a blank space in the editor reads as a broken block.
				EmptyResponsePlaceholder: function () {
					return el(
						components.Placeholder,
						{
							icon: 'smartphone',
							label: __( 'DiceBar', 'dicebar' ),
						},
						__(
							'No items to show yet. Add some on the DiceBar settings screen, then come back.',
							'dicebar'
						)
					);
				},
			} );

			return el(
				'div',
				blockEditor.useBlockProps ? blockEditor.useBlockProps() : {},
				inspector,
				body
			);
		},

		// Rendered by PHP on every request, so nothing is stored in the post
		// content and an edit to the settings reaches posts already published.
		save: function () {
			return null;
		},
	} );
}(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n,
	window.wp.serverSideRender
) );
