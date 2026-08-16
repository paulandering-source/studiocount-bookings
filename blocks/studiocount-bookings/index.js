( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	var el = element.createElement;
	var registerBlockType = blocks.registerBlockType;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps = blockEditor.useBlockProps;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var Notice = components.Notice;
	var __ = i18n.__;

	registerBlockType( 'studiocount/bookings', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var context = window.StudioCountBookingsEditor || {};
			var studio = String( context.defaultStudio || '' ).trim();
			var connected = true === context.connected;
			var view = attributes.view || context.defaultView || 'both';
			var viewLabel = {
				classes: __( 'Classes', 'studiocount-bookings' ),
				products: __( 'Products', 'studiocount-bookings' ),
				both: __( 'Classes and products', 'studiocount-bookings' )
			}[ view ] || __( 'Classes and products', 'studiocount-bookings' );
			var blockProps = useBlockProps( { className: 'studiocount-bookings-editor' } );

			return el(
				element.Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'StudioCount Bookings', 'studiocount-bookings' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Display', 'studiocount-bookings' ),
							value: attributes.view || '',
							options: [
								{ label: __( 'Use saved default', 'studiocount-bookings' ), value: '' },
								{ label: __( 'Classes', 'studiocount-bookings' ), value: 'classes' },
								{ label: __( 'Products', 'studiocount-bookings' ), value: 'products' },
								{ label: __( 'Classes and products', 'studiocount-bookings' ), value: 'both' }
							],
							onChange: function ( value ) {
								props.setAttributes( { view: value } );
							}
						} )
					)
				),
				el(
					'div',
					blockProps,
					el( 'p', { className: 'studiocount-bookings-editor__eyebrow' }, 'StudioCount' ),
					el( 'h3', {}, __( 'StudioCount Bookings', 'studiocount-bookings' ) ),
					studio && connected
						? el(
							'div',
							{ className: 'studiocount-bookings-editor__preview' },
							el( 'strong', {}, studio ),
							el( 'span', {}, viewLabel ),
							el( 'p', {}, __( 'The live page will show current information directly from StudioCount.', 'studiocount-bookings' ) )
						)
						: el(
							Notice,
							{ status: 'warning', isDismissible: false },
							__( 'Connect this website in StudioCount Bookings settings.', 'studiocount-bookings' )
						)
				)
			);
		},
		save: function () {
			return null;
		}
	} );
}(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n
) );
