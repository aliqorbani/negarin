<?php
/**
 * Registers two native Gutenberg blocks, both outside the WooCommerce
 * space — for plain WordPress pages built in the block editor (e.g.
 * "درباره ما") that need CTA buttons styled with the exact same
 * .btn / .btn--solid classes used everywhere else in the theme:
 *
 * - negarin/icon-button:  a single button with an icon next to the label.
 * - negarin/icon-buttons: a flex row that holds several icon-button
 *   blocks side by side (or stacked on mobile).
 *
 * @package Negarin
 */

namespace Negarin\Services;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IconButtonBlock {

    /**
     * Icons offered to editors, keyed the same way as negarin_social_icon().
     * Only platforms that already have real artwork there are listed —
     * add a label here the moment a new SVG lands in that helper.
     */
    private const ICON_LABELS = array(
        'instagram' => 'اینستاگرام',
        'telegram'  => 'تلگرام',
        'bale'      => 'بله',
    );

    public function __construct() {
        add_action( 'init', array( $this, 'register_block' ) );
        add_filter( 'block_categories_all', array( $this, 'add_block_category' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
    }

    /**
     * Groups the block under its own "نگارین" heading in the inserter
     * instead of dumping it into "Widgets".
     */
    public function add_block_category( array $categories ): array {
        return array_merge(
            array(
                array(
                    'slug'  => 'negarin',
                    'title' => __( 'نگارین', 'negarin' ),
                ),
            ),
            $categories
        );
    }

    public function register_block(): void {
        wp_register_script(
            'negarin-icon-button-editor',
            NEGARIN_URI . '/inc/blocks/icon-button/index.js',
            array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
            NEGARIN_VERSION,
            true
        );

        wp_localize_script(
            'negarin-icon-button-editor',
            'negarinIconButtonData',
            array(
                'icons' => $this->available_icons(),
            )
        );

        register_block_type( NEGARIN_DIR . '/inc/blocks/icon-button' );

        wp_register_script(
            'negarin-icon-buttons-editor',
            NEGARIN_URI . '/inc/blocks/icon-buttons/index.js',
            array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
            NEGARIN_VERSION,
            true
        );

        register_block_type( NEGARIN_DIR . '/inc/blocks/icon-buttons' );
    }

    /**
     * Filters ICON_LABELS down to platforms negarin_social_icon() can
     * actually draw right now, so the dropdown never offers a blank icon.
     */
    private function available_icons(): array {
        $icons = array();

        foreach ( self::ICON_LABELS as $key => $label ) {
            if ( function_exists( 'negarin_social_icon' ) && '' !== negarin_social_icon( $key ) ) {
                $icons[ $key ] = $label;
            }
        }

        return $icons;
    }

    /**
     * Same SVG artwork used across the site (footer socials, etc.),
     * recolored to follow the button's text color via currentColor
     * instead of the fixed footer-badge gray.
     */
    public static function icon_svg( string $key ): string {
        if ( '' === $key || 'none' === $key || ! function_exists( 'negarin_social_icon' ) ) {
            return '';
        }

        $svg = negarin_social_icon( $key );

        if ( '' === $svg ) {
            return '';
        }

        return str_replace( 'fill="#667085"', 'fill="currentColor"', $svg );
    }

    /**
     * Loads the theme's Tailwind CSS inside the block editor so the
     * ServerSideRender preview matches the front end exactly — the editor
     * screen never enqueues this file on its own otherwise.
     *
     * Mirrors the dev/build branching in inc/hooks/enqueue.php: on `npm run
     * dev` (Laragon) there is no manifest yet, so we ask the Vite dev server
     * directly for the transformed CSS file. We deliberately request the
     * .css file on its own here rather than app.js — app.js also boots
     * Alpine and Turbo Drive, which have no business running inside
     * wp-admin just to preview a button.
     */
    public function enqueue_editor_assets(): void {
        if ( function_exists( 'negarin_is_vite_dev' ) && negarin_is_vite_dev() ) {
            wp_enqueue_style( 'negarin-app-editor', 'http://localhost:5173/assets/css/app.css', array(), null );
            return;
        }

        $manifest_path = NEGARIN_DIR . '/assets/build/.vite/manifest.json';

        if ( ! file_exists( $manifest_path ) ) {
            return;
        }

        $manifest = json_decode( (string) file_get_contents( $manifest_path ), true );
        $entry    = $manifest['assets/js/app.js'] ?? null;

        if ( ! empty( $entry['css'][0] ) ) {
            wp_enqueue_style( 'negarin-app-editor', NEGARIN_URI . '/assets/build/' . $entry['css'][0], array(), NEGARIN_VERSION );
        }
    }
}