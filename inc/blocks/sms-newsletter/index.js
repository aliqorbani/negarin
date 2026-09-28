/**
 * Editor-side registration for negarin/sms-newsletter.
 *
 * Same approach as negarin/icon-button: plain JS against the wp.*
 * globals — no JSX, no bundler. The actual form markup lives in
 * template-parts/components/sms-newsletter-form.php (shared with the
 * [negarin_sms_newsletter] shortcode); this file only edits the three
 * text attributes and previews the real PHP output via ServerSideRender.
 */
( function ( blocks, element, blockEditor, components, i18n, serverSideRender ) {
	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;

	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var ServerSideRender = serverSideRender && ( serverSideRender.default || serverSideRender );

	blocks.registerBlockType( 'negarin/sms-newsletter', {
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
						{ title: __( 'تنظیمات فرم', 'negarin' ), initialOpen: true },
						el( TextControl, {
							label: __( 'عنوان', 'negarin' ),
							value: attributes.title,
							onChange: function ( value ) {
								setAttributes( { title: value } );
							},
						} ),
						el( TextareaControl, {
							label: __( 'توضیحات', 'negarin' ),
							value: attributes.description,
							onChange: function ( value ) {
								setAttributes( { description: value } );
							},
						} ),
						el( TextControl, {
							label: __( 'متن دکمه', 'negarin' ),
							value: attributes.buttonText,
							onChange: function ( value ) {
								setAttributes( { buttonText: value } );
							},
						} )
					)
				),
				el(
					'div',
					{ className: 'negarin-sms-newsletter-editor' },
					ServerSideRender
						? el( ServerSideRender, {
							block: 'negarin/sms-newsletter',
							attributes: attributes,
						} )
						: null
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n,
	window.wp.serverSideRender
);
