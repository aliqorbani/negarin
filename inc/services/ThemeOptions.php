<?php
/**
 * Site-wide Theme Options (ACF Options Page) — header/footer/contact/social/
 * announcement bar/tracking codes, editable from wp-admin without code changes.
 *
 * @package Negarin
 */

namespace Negarin\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ThemeOptions {

	public function __construct() {
		add_action( 'acf/init', array( $this, 'register_options_page' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register_options_page(): void {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		acf_add_options_page(
			array(
				'page_title' => __( 'Theme Options', 'negarin' ),
				'menu_title' => __( 'Theme Options', 'negarin' ),
				'menu_slug'  => 'negarin-theme-options',
				'capability' => 'manage_options',
				'icon_url'   => 'dashicons-admin-customizer',
			)
		);

		acf_add_options_sub_page(
			array(
				'page_title'  => __( 'Header & Announcement', 'negarin' ),
				'menu_title'  => __( 'Header', 'negarin' ),
                'menu_slug'   => 'negarin-header',
                'parent_slug' => 'negarin-theme-options',
			)
		);

		acf_add_options_sub_page(
			array(
				'page_title'  => __( 'Footer & Contact', 'negarin' ),
				'menu_title'  => __( 'Footer', 'negarin' ),
                'menu_slug'   => 'negarin-footer',
                'parent_slug' => 'negarin-theme-options',
			)
		);

		acf_add_options_sub_page(
			array(
				'page_title'  => __( 'Tracking Codes', 'negarin' ),
				'menu_title'  => __( 'Tracking', 'negarin' ),
                'menu_slug'   => 'negarin-tracking',
                'parent_slug' => 'negarin-theme-options',
			)
		);

		acf_add_options_sub_page(
			array(
				'page_title'  => __( 'Custom Order Sizing', 'negarin' ),
				'menu_title'  => __( 'Sizing Presets', 'negarin' ),
                'menu_slug'   => 'negarin-sizing',
				'parent_slug' => 'negarin-theme-options',
			)
		);

		acf_add_options_sub_page(
			array(
				'page_title'  => __( 'Login Screen', 'negarin' ),
				'menu_title'  => __( 'Login Screen', 'negarin' ),
                'menu_slug'   => 'negarin-login-screen',
				'parent_slug' => 'negarin-theme-options',
			)
		);
	}

	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'    => 'group_negarin_theme_options',
				'title'  => __( 'Site Identity', 'negarin' ),
				'fields' => array(
					array(
						'key'   => 'field_favicon',
						'name'  => 'favicon',
						'label' => __( 'Favicon', 'negarin' ),
						'type'  => 'image',
						'return_format' => 'id',
					),
					array(
						'key'   => 'field_announcement_enabled',
						'name'  => 'announcement_enabled',
						'label' => __( 'Show Announcement Bar', 'negarin' ),
						'type'  => 'true_false',
						'ui'    => 1,
					),
					array(
						'key'   => 'field_announcement_text',
						'name'  => 'announcement_text',
						'label' => __( 'Announcement Text', 'negarin' ),
						'type'  => 'text',
					),
					array(
						'key'   => 'field_announcement_link',
						'name'  => 'announcement_link',
						'label' => __( 'Announcement Link', 'negarin' ),
						'type'  => 'link',
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'negarin-header',
						),
					),
				),
			)
		);

		acf_add_local_field_group(
			array(
				'key'    => 'group_negarin_footer_options',
				'title'  => __( 'Footer & Contact', 'negarin' ),
				'fields' => array(
					array(
						'key'   => 'field_footer_text',
						'name'  => 'footer_text',
						'label' => __( 'Footer Description', 'negarin' ),
						'type'  => 'textarea',
					),
					array(
						'key'   => 'field_phone',
						'name'  => 'phone',
						'label' => __( 'Phone', 'negarin' ),
						'type'  => 'text',
					),
					array(
						'key'   => 'field_email',
						'name'  => 'email',
						'label' => __( 'Email', 'negarin' ),
						'type'  => 'email',
					),
					array(
						'key'          => 'field_socials',
						'name'         => 'socials',
						'label'        => __( 'Social Links', 'negarin' ),
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => __( 'Add Social Link', 'negarin' ),
						'sub_fields'   => array(
							array(
								'key'     => 'field_social_platform',
								'name'    => 'platform',
								'label'   => __( 'Platform', 'negarin' ),
								'type'    => 'select',
								'choices' => array(
									'instagram' => 'Instagram',
									'telegram'  => 'Telegram',
									'whatsapp'  => 'WhatsApp',
									'twitter'   => 'X / Twitter',
								),
							),
							array(
								'key'   => 'field_social_url',
								'name'  => 'url',
								'label' => __( 'URL', 'negarin' ),
								'type'  => 'url',
							),
						),
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'negarin-footer',
						),
					),
				),
			)
		);

		acf_add_local_field_group(
			array(
				'key'    => 'group_negarin_tracking_options',
				'title'  => __( 'Tracking Codes', 'negarin' ),
				'fields' => array(
					array(
						'key'   => 'field_head_scripts',
						'name'  => 'head_scripts',
						'label' => __( 'Scripts (before </head>)', 'negarin' ),
						'type'  => 'textarea',
						'rows'  => 6,
					),
					array(
						'key'   => 'field_body_scripts',
						'name'  => 'body_scripts',
						'label' => __( 'Scripts (before </body>)', 'negarin' ),
						'type'  => 'textarea',
						'rows'  => 6,
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'negarin-tracking',
						),
					),
				),
			)
		);
		acf_add_local_field_group(
			array(
				'key'    => 'group_negarin_sizing_options',
				'title'  => __( 'Custom Order Sizing', 'negarin' ),
				'fields' => array(
					array(
						'key'          => 'field_measurement_fields',
						'name'         => 'measurement_fields',
						'label'        => __( 'Measurement Fields', 'negarin' ),
						'instructions' => __( 'Each row becomes one measurement input in the custom-order modal (e.g. دور سینه، قد عبا، قد آستین). Presets are shown as a dropdown; the customer can switch to manual entry for any field.', 'negarin' ),
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => __( 'Add Measurement Field', 'negarin' ),
						'sub_fields'   => array(
							array(
								'key'   => 'field_mf_key',
								'name'  => 'key',
								'label' => __( 'Field Key (English, no spaces — e.g. chest)', 'negarin' ),
								'type'  => 'text',
								'required' => 1,
							),
							array(
								'key'   => 'field_mf_label',
								'name'  => 'label',
								'label' => __( 'Label (Persian) — e.g. دور سینه', 'negarin' ),
								'type'  => 'text',
								'required' => 1,
							),
							array(
								'key'   => 'field_mf_unit',
								'name'  => 'unit',
								'label' => __( 'Unit', 'negarin' ),
								'type'  => 'text',
								'default_value' => 'سانتی‌متر',
							),
							array(
								'key'          => 'field_mf_presets',
								'name'         => 'presets',
								'label'        => __( 'Preset Values', 'negarin' ),
								'type'         => 'repeater',
								'layout'       => 'table',
								'button_label' => __( 'Add Preset', 'negarin' ),
								'sub_fields'   => array(
									array(
										'key'   => 'field_mf_preset_label',
										'name'  => 'label',
										'label' => __( 'Shown Label (e.g. "90 - 94")', 'negarin' ),
										'type'  => 'text',
									),
									array(
										'key'   => 'field_mf_preset_value',
										'name'  => 'value',
										'label' => __( 'Stored Value (e.g. 92)', 'negarin' ),
										'type'  => 'text',
									),
								),
							),
						),
					),
					array(
						'key'          => 'field_size_guide_items',
						'name'         => 'size_guide_items',
						'label'        => __( 'Size Guide Items', 'negarin', ),
						'instructions' => __( 'Each row is one lettered point on the size-guide illustration (A: قد, B: دور سینه, ...).', 'negarin' ),
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => __( 'Add Item', 'negarin' ),
						'sub_fields'   => array(
							array(
								'key'   => 'field_sg_letter',
								'name'  => 'letter',
								'label' => __( 'Letter', 'negarin' ),
								'type'  => 'text',
								'wrapper' => array( 'width' => 15 ),
							),
							array(
								'key'   => 'field_sg_title',
								'name'  => 'title',
								'label' => __( 'Title', 'negarin' ),
								'type'  => 'text',
								'wrapper' => array( 'width' => 35 ),
							),
							array(
								'key'   => 'field_sg_description',
								'name'  => 'description',
								'label' => __( 'Description', 'negarin' ),
								'type'  => 'textarea',
								'rows'  => 3,
								'wrapper' => array( 'width' => 50 ),
							),
						),
					),
					array(
						'key'   => 'field_size_guide_image',
						'name'  => 'size_guide_image',
						'label' => __( 'Size Guide Illustration', 'negarin' ),
						'type'  => 'image',
						'return_format' => 'id',
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'negarin-sizing',
						),
					),
				),
			)
		);
		acf_add_local_field_group(
			array(
				'key'    => 'group_negarin_login_options',
				'title'  => __( 'Login Screen', 'negarin' ),
				'fields' => array(
					array(
						'key'   => 'field_login_image',
						'name'  => 'login_image',
						'label' => __( 'Decorative Image (right/side panel)', 'negarin' ),
						'type'  => 'image',
						'return_format' => 'id',
					),
					array(
						'key'   => 'field_login_welcome_text',
						'name'  => 'login_welcome_text',
						'label' => __( 'Welcome Card Text (on the image)', 'negarin' ),
						'type'  => 'textarea',
						'default_value' => __( "به خانه نگارین\nخوش آمدید", 'negarin' ),
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'negarin-login-screen',
						),
					),
				),
			)
		);
	}
}
