<?php
/**
 * Extra per-product editorial fields shown in the single-product accordions:
 * "مشخصات محصول" (specifications) and "مراقبت از محصول" (care instructions).
 *
 * @package Negarin
 */

namespace Negarin\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductFields {

	public function __construct() {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_negarin_product_details',
				'title'    => __( 'Product Detail Accordions', 'negarin' ),
				'fields'   => array(
					array(
						'key'     => 'field_product_specs',
						'name'    => 'specifications',
						'label'   => __( 'مشخصات محصول', 'negarin' ),
						'type'    => 'wysiwyg',
						'tabs'    => 'text',
						'toolbar' => 'basic',
						'media_upload' => 0,
					),
					array(
						'key'     => 'field_product_care',
						'name'    => 'care_instructions',
						'label'   => __( 'مراقبت از محصول', 'negarin' ),
						'type'    => 'wysiwyg',
						'tabs'    => 'text',
						'toolbar' => 'basic',
						'media_upload' => 0,
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'product',
						),
					),
				),
				'position' => 'normal',
				'style'    => 'default',
			)
		);
	}
}
