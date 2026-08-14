<?php
/**
 * Registers the ACF Flexible Content field group that drives the homepage
 * (and any other "builder" page) — this is the reusable section system
 * requested by the client: every section's layout is editor-selectable.
 *
 * @package Negarin
 */

namespace Negarin\Services;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FlexibleContent {

    public function __construct() {
        add_action( 'acf/init', array( $this, 'register_page_builder' ) );
        add_action( 'acf/init', array( $this, 'register_post_type_support' ) );
    }

    /**
     * Allow the builder field group on Pages (homepage is just a Page using
     * the `page-builder.php` template) and optionally on a "Landing" CPT later.
     */
    public function register_post_type_support(): void {
        // no-op placeholder for future CPT hookups (kept for architecture consistency).
    }

    public function register_page_builder(): void {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group(
            array(
                'key'      => 'group_negarin_page_builder',
                'title'    => __( 'Page Sections (Builder)', 'negarin' ),
                'fields'   => array(
                    array(
                        'key'          => 'field_negarin_sections',
                        'label'        => __( 'Sections', 'negarin' ),
                        'name'         => 'sections',
                        'type'         => 'flexible_content',
                        'button_label' => __( 'Add Section', 'negarin' ),
                        'layouts'      => $this->layouts(),
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param'    => 'page_template',
                            'operator' => '==',
                            'value'    => 'templates/page-builder.php',
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * All available section layouts. Add a new layout here and a matching
     * template-parts/sections/{name}.php partial — nothing else to wire up.
     */
    private function layouts(): array {
        $layouts = array(
            $this->layout_hero(),
            $this->layout_image_text(),
            $this->layout_image_grid(),
            $this->layout_banner(),
            $this->layout_product_carousel(),
        );

        // ACF's local (PHP array) field-group format requires `layouts` to
        // be keyed by each layout's own `key`, not a plain numeric list —
        // otherwise ACF silently renders an empty field with no "Add
        // Section" button and no error.
        $keyed = array();
        foreach ( $layouts as $layout ) {
            $keyed[ $layout['key'] ] = $layout;
        }

        return $keyed;
    }

    /**
     * Full-bleed hero: single image, optional headline/subtext/CTA overlay.
     */
    private function layout_hero(): array {
        return array(
            'key'    => 'layout_hero',
            'name'   => 'hero',
            'label'  => __( 'Hero (full width)', 'negarin' ),
            'sub_fields' => array(
                $this->image_field( 'hero_image', 'Image' ),
                $this->text_field( 'hero_eyebrow', 'Eyebrow (small label)' ),
                $this->text_field( 'hero_title', 'Title' ),
                $this->textarea_field( 'hero_text', 'Description' ),
                $this->link_field( 'hero_button', 'Button' ),
                array(
                    'key'     => 'field_hero_overlay_position',
                    'name'    => 'text_position',
                    'label'   => __( 'Text Position', 'negarin' ),
                    'type'    => 'select',
                    'choices' => array(
                        'center' => __( 'Center', 'negarin' ),
                        'bottom' => __( 'Bottom', 'negarin' ),
                        'none'   => __( 'No overlay text', 'negarin' ),
                    ),
                    'default_value' => 'none',
                ),
            ),
        );
    }

    /**
     * Image + text split section — the "عکس راست / متن چپ یا برعکس" layout.
     */
    private function layout_image_text(): array {
        return array(
            'key'        => 'layout_image_text',
            'name'       => 'image_text',
            'label'      => __( 'Image + Text', 'negarin' ),
            'sub_fields' => array(
                array(
                    'key'           => 'field_it_side',
                    'name'          => 'image_side',
                    'label'         => __( 'Image Position', 'negarin' ),
                    'type'          => 'button_group',
                    'choices'       => array(
                        'right' => __( 'Image right / Text left', 'negarin' ),
                        'left'  => __( 'Image left / Text right', 'negarin' ),
                    ),
                    'default_value' => 'right',
                ),
                $this->image_field( 'it_image', 'Image' ),
                $this->link_field( 'it_image_link', 'Image Link (optional — makes the image clickable on hover, e.g. link to a product)' ),
                $this->text_field( 'it_eyebrow', 'Eyebrow (small label, e.g. product name)' ),
                $this->text_field( 'it_title', 'Title' ),
                $this->textarea_field( 'it_text', 'Description' ),
                $this->link_field( 'it_button', 'Button (optional)' ),
                array(
                    'key'   => 'field_it_bg',
                    'name'  => 'background_color',
                    'label' => __( 'Section Background', 'negarin' ),
                    'type'  => 'select',
                    'choices' => array(
                        'white' => __( 'White', 'negarin' ),
                        'cream' => __( 'Cream', 'negarin' ),
                        'black' => __( 'Black', 'negarin' ),
                    ),
                    'default_value' => 'white',
                ),
            ),
        );
    }

    /**
     * Multi-image grid — "دوتا عکس کنار هم" / "سه تا عکس کنار هم".
     */
    private function layout_image_grid(): array {
        return array(
            'key'        => 'layout_image_grid',
            'name'       => 'image_grid',
            'label'      => __( 'Image Grid (2 or 3 across)', 'negarin' ),
            'sub_fields' => array(
                array(
                    'key'           => 'field_grid_columns',
                    'name'          => 'columns',
                    'label'         => __( 'Columns', 'negarin' ),
                    'type'          => 'button_group',
                    'choices'       => array(
                        '2' => '2',
                        '3' => '3',
                    ),
                    'default_value' => '2',
                ),
                array(
                    'key'          => 'field_grid_items',
                    'name'         => 'items',
                    'label'        => __( 'Images', 'negarin' ),
                    'type'         => 'repeater',
                    'min'          => 2,
                    'max'          => 3,
                    'layout'       => 'table',
                    'button_label' => __( 'Add Image', 'negarin' ),
                    'sub_fields'   => array(
                        $this->image_field( 'image', 'Image' ),

                        $this->text_field( 'caption', 'Caption (optional)' ),
                        $this->link_field( 'link', 'Link (optional)' ),
                    ),
                ),
            ),
        );
    }

    /**
     * Simple full-width single/duo banner with no text (packaging shots etc.).
     */
    private function layout_banner(): array {
        return array(
            'key'        => 'layout_banner',
            'name'       => 'banner',
            'label'      => __( 'Banner (image only)', 'negarin' ),
            'sub_fields' => array(
                $this->image_field( 'banner_image', 'Image' ),
                $this->link_field( 'banner_link', 'Link (optional)' ),
            ),
        );
    }

    /**
     * Product carousel pulling from a manually curated list or a WooCommerce
     * category — used for "محصولات پیشنهادی" style rows.
     */
    private function layout_product_carousel(): array {
        return array(
            'key'        => 'layout_product_carousel',
            'name'       => 'product_carousel',
            'label'      => __( 'Product Carousel', 'negarin' ),
            'sub_fields' => array(
                $this->text_field( 'pc_title', 'Section Title' ),
                array(
                    'key'     => 'field_pc_source',
                    'name'    => 'source',
                    'label'   => __( 'Source', 'negarin' ),
                    'type'    => 'select',
                    'choices' => array(
                        'category' => __( 'WooCommerce Category', 'negarin' ),
                        'manual'   => __( 'Manually Selected Products', 'negarin' ),
                    ),
                    'default_value' => 'category',
                ),
                array(
                    'key'               => 'field_pc_category',
                    'name'              => 'category',
                    'label'             => __( 'Category', 'negarin' ),
                    'type'              => 'taxonomy',
                    'taxonomy'          => 'product_cat',
                    'field_type'        => 'select',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'    => 'field_pc_source',
                                'operator' => '==',
                                'value'    => 'category',
                            ),
                        ),
                    ),
                ),
                array(
                    'key'               => 'field_pc_products',
                    'name'              => 'products',
                    'label'             => __( 'Products', 'negarin' ),
                    'type'              => 'relationship',
                    'post_type'         => array( 'product' ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'    => 'field_pc_source',
                                'operator' => '==',
                                'value'    => 'manual',
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /* ---------------------------------------------------------------------
     * Small field factories to avoid repeating boilerplate arrays (DRY).
     * ------------------------------------------------------------------- */

    private function image_field( string $name, string $label ): array {
        return array(
            'key'           => 'field_' . $name,
            'name'          => $name,
            'label'         => __( $label, 'negarin' ),
            'type'          => 'image',
            'return_format' => 'id',
            'preview_size'  => 'medium',
        );
    }

    private function text_field( string $name, string $label ): array {
        return array(
            'key'   => 'field_' . $name,
            'name'  => $name,
            'label' => __( $label, 'negarin' ),
            'type'  => 'text',
        );
    }

    private function textarea_field( string $name, string $label ): array {
        return array(
            'key'   => 'field_' . $name,
            'name'  => $name,
            'label' => __( $label, 'negarin' ),
            'type'  => 'textarea',
            'rows'  => 3,
        );
    }

    private function link_field( string $name, string $label ): array {
        return array(
            'key'   => 'field_' . $name,
            'name'  => $name,
            'label' => __( $label, 'negarin' ),
            'type'  => 'link',
        );
    }
}