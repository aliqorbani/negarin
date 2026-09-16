/**
 * Editor-side registration for negarin/icon-buttons — a plain flex
 * container that only accepts negarin/icon-button children, so several
 * buttons can sit side by side (or stack on mobile via wrapping).
 *
 * Unlike icon-button, this block has no server logic — its markup is just
 * a wrapper div plus whatever InnerBlocks holds — so it uses a normal
 * static save() instead of ServerSideRender.
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;

	var InnerBlocks = blockEditor.InnerBlocks;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var ToggleControl = components.ToggleControl;

	var JUSTIFY_CLASS = {
		center: 'negarin-icon-buttons--center',
		end: 'negarin-icon-buttons--end',
		between: 'negarin-icon-buttons--between',
	};

	function wrapperClassName( attributes ) {
		var classes = [ 'negarin-icon-buttons' ];
		if ( JUSTIFY_CLASS[ attributes.justify ] ) {
			classes.push( JUSTIFY_CLASS[ attributes.justify ] );
		}
		if ( ! attributes.wrap ) {
			classes.push( 'negarin-icon-buttons--nowrap' );
		}
		return classes.join( ' ' );
	}

	blocks.registerBlockType( 'negarin/icon-buttons', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'چیدمان', 'negarin' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'چینش دکمه‌ها', 'negarin' ),
							value: attributes.justify,
							options: [
								{ label: __( 'راست (پیش‌فرض)', 'negarin' ), value: 'start' },
								{ label: __( 'وسط', 'negarin' ), value: 'center' },
								{ label: __( 'چپ', 'negarin' ), value: 'end' },
								{ label: __( 'پخش با فاصله مساوی', 'negarin' ), value: 'between' },
							],
							onChange: function ( value ) {
								setAttributes( { justify: value } );
							},
						} ),
						el( ToggleControl, {
							label: __( 'شکستن به خط بعد در صفحه‌های کوچک', 'negarin' ),
							checked: attributes.wrap,
							onChange: function ( value ) {
								setAttributes( { wrap: value } );
							},
						} )
					)
				),
				el(
					'div',
					{ className: wrapperClassName( attributes ) },
					el( InnerBlocks, {
						allowedBlocks: [ 'negarin/icon-button' ],
						template: [ [ 'negarin/icon-button', {} ] ],
						orientation: 'horizontal',
					} )
				)
			);
		},
		save: function ( props ) {
			return el(
				'div',
				{ className: wrapperClassName( props.attributes ) },
				el( InnerBlocks.Content, {} )
			);
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n
);