/**
 * Editor-side registration for negarin/icon-button.
 *
 * Deliberately plain JS against the wp.* globals — no JSX, no bundler —
 * to match the rest of the theme (Alpine.js + hand-rolled files, no
 * Composer/webpack build step for admin-only code). The actual button
 * markup lives in render.php; this file only builds the settings UI and
 * asks WordPress to preview that same PHP output via ServerSideRender,
 * so the editor can never drift out of sync with the front end.
 *
 * The most-used controls (text, solid/outline) sit directly on the block
 * in the canvas — a toolbar toggle and an inline field — instead of only
 * inside InspectorControls, which lives in the Settings sidebar's "Block"
 * tab and is easy to miss if that panel isn't open. Less-used options
 * (icon, link target, icon position, the outline-white variant) stay in
 * the sidebar.
 */
( function ( blocks, element, blockEditor, components, i18n, serverSideRender ) {
	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;

	var InspectorControls = blockEditor.InspectorControls;
	var BlockControls = blockEditor.BlockControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var SelectControl = components.SelectControl;
	var ToggleControl = components.ToggleControl;
	var ToolbarGroup = components.ToolbarGroup;
	var ToolbarButton = components.ToolbarButton;
	var ServerSideRender = serverSideRender && ( serverSideRender.default || serverSideRender );

	var availableIcons = ( window.negarinIconButtonData && window.negarinIconButtonData.icons ) || {};

	function iconOptions() {
		var options = [ { label: __( 'بدون آیکن', 'negarin' ), value: 'none' } ];
		Object.keys( availableIcons ).forEach( function ( key ) {
			options.push( { label: availableIcons[ key ], value: key } );
		} );
		return options;
	}

	blocks.registerBlockType( 'negarin/icon-button', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				Fragment,
				{},
				el(
					BlockControls,
					{},
					el(
						ToolbarGroup,
						{},
						el( ToolbarButton, {
							text: __( 'توپر مشکی', 'negarin' ),
							isPressed: 'solid' === attributes.variant,
							onClick: function () {
								setAttributes( { variant: 'solid' } );
							},
						} ),
						el( ToolbarButton, {
							text: __( 'خط‌دار', 'negarin' ),
							isPressed: 'outline' === attributes.variant || 'outline-white' === attributes.variant,
							onClick: function () {
								setAttributes( { variant: 'outline' } );
							},
						} )
					)
				),
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'تنظیمات دکمه', 'negarin' ), initialOpen: true },
						el( TextControl, {
							label: __( 'متن دکمه', 'negarin' ),
							value: attributes.text,
							onChange: function ( value ) {
								setAttributes( { text: value } );
							},
						} ),
						el( TextControl, {
							label: __( 'آدرس لینک', 'negarin' ),
							value: attributes.url,
							placeholder: 'https://instagram.com/negarin',
							onChange: function ( value ) {
								setAttributes( { url: value } );
							},
						} ),
						el( ToggleControl, {
							label: __( 'باز شدن در تب جدید', 'negarin' ),
							checked: '_blank' === attributes.linkTarget,
							onChange: function ( checked ) {
								setAttributes( { linkTarget: checked ? '_blank' : '_self' } );
							},
						} ),
						el( SelectControl, {
							label: __( 'آیکن', 'negarin' ),
							value: attributes.icon,
							options: iconOptions(),
							onChange: function ( value ) {
								setAttributes( { icon: value } );
							},
						} ),
						el( SelectControl, {
							label: __( 'موقعیت آیکن', 'negarin' ),
							value: attributes.iconPosition,
							options: [
								{ label: __( 'بعد از متن', 'negarin' ), value: 'after' },
								{ label: __( 'قبل از متن', 'negarin' ), value: 'before' },
							],
							onChange: function ( value ) {
								setAttributes( { iconPosition: value } );
							},
						} ),
						el( SelectControl, {
							label: __( 'استایل دکمه', 'negarin' ),
							value: attributes.variant,
							options: [
								{ label: __( 'توپر مشکی (btn--solid)', 'negarin' ), value: 'solid' },
								{ label: __( 'خط‌دار (btn--outline)', 'negarin' ), value: 'outline' },
								{ label: __( 'خط‌دار سفید (btn--outline-white)', 'negarin' ), value: 'outline-white' },
							],
							onChange: function ( value ) {
								setAttributes( { variant: value } );
							},
						} )
					)
				),
				el(
					'div',
					{ className: 'negarin-icon-btn-editor' },
					el( TextControl, {
						label: __( 'متن دکمه', 'negarin' ),
						value: attributes.text,
						onChange: function ( value ) {
							setAttributes( { text: value } );
						},
					} ),
					el(
						'div',
						{ className: 'negarin-icon-btn-editor-preview' },
						ServerSideRender
							? el( ServerSideRender, {
								block: 'negarin/icon-button',
								attributes: attributes,
							} )
							: null
					)
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