<?php
/**
 * "انتخاب سایز" — real WooCommerce variable-product sizing.
 *
 * Every abaya is a Variable Product using one global attribute, "سایز"
 * (taxonomy `pa_size`, numeric terms 38..52). This class:
 *
 *  1. Provisions that attribute + its terms in code on `init`, so a fresh
 *     environment never needs a manual "add attribute" step in wp-admin —
 *     consistent with the project's constants/code-first conventions. This
 *     only ever inserts rows the first time they're missing; safe to run
 *     on every request. It also prunes any retired terms (see
 *     prune_retired_terms()) left over from the old 32–56 range or the
 *     removed custom-order flow.
 *  2. Provides `is_sized_product()` / `get_size_options()` so templates
 *     don't need to know taxonomy internals.
 *  3. Exposes POST /wp-json/negarin/v1/size-select/add-to-cart, the AJAX
 *     endpoint the size-select modal posts a chosen variation to.
 *
 * Per the 2026-09 decision, the store doesn't do inventory management:
 * there's no "سفارش شخصی" (custom order) fallback anymore, and every size
 * a product has a variation for is selectable regardless of that
 * variation's own stock fields — see ensure_no_stock_management().
 *
 * @package Negarin
 */

namespace Negarin\Services;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ProductSizing {

    /**
     * Attribute slug as passed to wc_create_attribute()/wc_attribute_taxonomy_name() —
     * WooCommerce prefixes this to the real taxonomy name `pa_size`.
     */
    public const ATTRIBUTE_SLUG = 'size';

    /**
     * The store's full standard size run. Sizes not relevant to a given
     * product simply aren't added as terms on that product — this is just
     * the universe of terms the attribute can ever contain.
     */
    private const STANDARD_SIZES = array( 38, 40, 42, 44, 46, 48, 50, 52 );

    public function __construct() {
        // Priority 0: runs before WC_Post_Types::register_taxonomies() (priority 5),
        // so a newly-created attribute is registered as a taxonomy this same request.
        add_action( 'init', array( $this, 'ensure_attribute_exists' ), 0 );
        // Priority 20: runs after the taxonomy above is registered, so wp_insert_term() works.
        add_action( 'init', array( $this, 'ensure_terms_exist' ), 20 );
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public static function taxonomy(): string {
        return wc_attribute_taxonomy_name( self::ATTRIBUTE_SLUG );
    }

    public function ensure_attribute_exists(): void {
        if ( ! function_exists( 'wc_attribute_taxonomy_name' ) || ! function_exists( 'wc_create_attribute' ) ) {
            return;
        }

        if ( taxonomy_exists( self::taxonomy() ) ) {
            return;
        }

        // Guards against wc_attribute_taxonomy_id_by_name() finding a row
        // that exists in the DB but hasn't been (re)registered as a
        // taxonomy yet in this request (e.g. right after activation).
        if ( wc_attribute_taxonomy_id_by_name( self::ATTRIBUTE_SLUG ) ) {
            return;
        }

        wc_create_attribute(
            array(
                'name'         => __( 'سایز', 'negarin' ),
                'slug'         => self::ATTRIBUTE_SLUG,
                'type'         => 'select',
                'order_by'     => 'menu_order',
                'has_archives' => false,
            )
        );
    }

    public function ensure_terms_exist(): void {
        $taxonomy = self::taxonomy();

        if ( ! taxonomy_exists( $taxonomy ) ) {
            return;
        }

        foreach ( self::STANDARD_SIZES as $order => $size ) {
            if ( term_exists( (string) $size, $taxonomy ) ) {
                continue;
            }

            $result = wp_insert_term( (string) $size, $taxonomy );

            if ( ! is_wp_error( $result ) && isset( $result['term_id'] ) ) {
                wp_update_term( $result['term_id'], $taxonomy, array( 'menu_order' => $order ) );
            }
        }

        $this->prune_retired_terms( $taxonomy );
    }

    /**
     * Removes pa_size terms that are no longer part of the store's size
     * range — the old 32/34/36/54/56 sizes dropped when the run narrowed
     * to 38–52, and the legacy "سفارش شخصی" term from before the custom
     * order flow was removed. A term still assigned to a product's
     * attribute list is left alone (and logged) rather than force-deleted,
     * since that would silently drop that product's declared size option;
     * that cleanup is a manual wp-admin edit.
     */
    private function prune_retired_terms( string $taxonomy ): void {
        $keep  = array_map( 'strval', self::STANDARD_SIZES );
        $terms = get_terms(
            array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            )
        );

        if ( is_wp_error( $terms ) ) {
            return;
        }

        foreach ( $terms as $term ) {
            if ( in_array( $term->name, $keep, true ) ) {
                continue;
            }

            if ( (int) $term->count > 0 ) {
                error_log(
                    sprintf(
                        'Negarin: retired pa_size term "%s" (slug: %s) is still assigned to %d product(s) — remove it from those products in wp-admin before it can be deleted.',
                        $term->name,
                        $term->slug,
                        $term->count
                    )
                );
                continue;
            }

            wp_delete_term( $term->term_id, $taxonomy );
        }
    }

    /**
     * Whether this product should use the "انتخاب سایز" flow (real
     * variations) rather than a plain add-to-cart button.
     */
    public static function is_sized_product( \WC_Product $product ): bool {
        return $product->is_type( 'variable' ) && self::has_size_attribute( $product );
    }

    private static function has_size_attribute( \WC_Product $product ): bool {
        foreach ( $product->get_attributes() as $attribute ) {
            if ( $attribute instanceof \WC_Product_Attribute && $attribute->get_name() === self::taxonomy() ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Everything the size-select modal needs, already resolved server-side:
     * every size term the product carries (in store order), and that
     * size's variation ID (0 when the product has no variation for it yet
     * — the button is shown, struck through, but not selectable). The
     * store doesn't manage inventory, so "in_stock" here just means "a
     * variation exists for this size" — it no longer reflects that
     * variation's own stock fields.
     *
     * @return array<int, array{term_id:int, slug:string, label:string, variation_id:int, in_stock:bool}>
     */
    public static function get_size_options( \WC_Product_Variable $product ): array {
        $taxonomy = self::taxonomy();
        $terms    = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'all' ) );

        if ( empty( $terms ) ) {
            return array();
        }

        usort( $terms, static fn( $a, $b ) => (int) $a->name <=> (int) $b->name );

        // Loading the cart's contents from the session happens lazily —
        // once, the first time anything actually asks for them — and
        // WC_Cart doesn't retry it if that first attempt gets disrupted.
        // Saving a product busts WordPress's post cache, which is
        // harmless on its own but if it happens to land in the middle of
        // that lazy load, an item already sitting in the customer's cart
        // can come out of it missing or wrong. ensure_no_stock_management()
        // below does exactly this kind of save on the parent and on every
        // variation, so priming the load here — before any of that runs —
        // sidesteps the whole interaction. Cheap and idempotent if it's
        // already loaded.
        if ( WC()->cart ) {
            WC()->cart->get_cart();
        }

        // The parent itself can also carry "Manage stock?" (Product data
        // → Inventory tab, separate from each variation's own Inventory
        // box) — when it's on, every variation whose own setting is left
        // at the "same as parent" default draws from ONE shared quantity
        // across all sizes combined, instead of each size being
        // independently selectable. Clear that first so a variation's own
        // manage_stock reading below isn't still inherited from a
        // stock-managing parent.
        self::ensure_no_stock_management( $product );

        // Read straight off the product's own children rather than
        // get_available_variations() — that method hides a variation
        // entirely when WooCommerce's "hide out of stock items" catalog
        // setting is on, which would make an out-of-stock size disappear
        // instead of just being selectable like every other size.
        $by_slug = array();
        foreach ( $product->get_children() as $variation_id ) {
            $variation = wc_get_product( $variation_id );

            if ( ! $variation instanceof \WC_Product_Variation || 'publish' !== $variation->get_status() ) {
                continue;
            }

            $raw_attributes = $variation->get_attributes(); // Flat ['pa_size' => 'raw-slug'].
            $slug           = $raw_attributes[ $taxonomy ] ?? '';

            if ( '' === $slug ) {
                continue;
            }

            self::ensure_no_stock_management( $variation );
            $by_slug[ $slug ] = $variation_id;
        }

        $options = array();
        foreach ( $terms as $term ) {
            $options[] = array(
                'term_id'      => $term->term_id,
                'slug'         => $term->slug,
                'label'        => self::to_persian_digits( $term->name ),
                'variation_id' => $by_slug[ $term->slug ] ?? 0,
                'in_stock'     => isset( $by_slug[ $term->slug ] ),
            );
        }

        return $options;
    }

    /**
     * Per the 2026-09 decision the store doesn't manage inventory: any
     * size the product has a variation for must stay selectable no matter
     * what its stock fields say (leftovers from earlier testing, a stray
     * admin edit, etc), and no two sizes of the same product should ever
     * draw from a shared stock pool. Rather than trust the parent product
     * or its variations to already be in that state, this normalizes
     * whichever one is passed in — called on both, see get_size_options()
     * — the first time it's touched, and saves the correction so it
     * sticks.
     */
    private static function ensure_no_stock_management( \WC_Product $product ): void {
        $changed = false;

        if ( $product->get_manage_stock() ) {
            $product->set_manage_stock( false );
            $changed = true;
        }

        if ( 'instock' !== $product->get_stock_status() ) {
            $product->set_stock_status( 'instock' );
            $changed = true;
        }

        if ( $changed ) {
            $product->save();
        }
    }

    /**
     * Term names are stored as plain "32", "34"... (kept ASCII so slug
     * matching against variation attributes stays simple) but the Figma
     * export shows Persian-Indic digits on the buttons themselves.
     */
    private static function to_persian_digits( string $value ): string {
        return strtr(
            $value,
            array(
                '0' => '۰',
                '1' => '۱',
                '2' => '۲',
                '3' => '۳',
                '4' => '۴',
                '5' => '۵',
                '6' => '۶',
                '7' => '۷',
                '8' => '۸',
                '9' => '۹',
            )
        );
    }

    public function register_routes(): void {
        register_rest_route(
            'negarin/v1',
            '/size-select/add-to-cart',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_add_to_cart' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'product_id'   => array( 'required' => true ),
                    'variation_id' => array( 'required' => true ),
                ),
            )
        );
    }

    public function handle_add_to_cart( WP_REST_Request $request ) {
        $product_id   = absint( $request->get_param( 'product_id' ) );
        $variation_id = absint( $request->get_param( 'variation_id' ) );
        $product      = wc_get_product( $product_id );

        if ( ! $product || ! $product->is_type( 'variable' ) ) {
            return new WP_Error( 'negarin_invalid_product', __( 'محصول یافت نشد.', 'negarin' ), array( 'status' => 404 ) );
        }

        // Load before get_size_options() below touches (and potentially
        // saves) any product/variation post — see the comment at the top
        // of get_size_options() for why the ordering matters.
        if ( ! WC()->cart ) {
            wc_load_cart();
        }

        $options = self::get_size_options( $product );
        $match   = null;

        foreach ( $options as $option ) {
            if ( $option['variation_id'] > 0 && $option['variation_id'] === $variation_id ) {
                $match = $option;
                break;
            }
        }

        if ( ! $match ) {
            return new WP_Error( 'negarin_size_unavailable', __( 'این سایز برای این محصول تعریف نشده است.', 'negarin' ), array( 'status' => 409 ) );
        }

        $variation_attributes = array(
            'attribute_' . self::taxonomy() => $match['slug'],
        );

        $cart_item_key = WC()->cart->add_to_cart( $product_id, 1, $variation_id, $variation_attributes );

        if ( ! $cart_item_key ) {
            $errors = wc_get_notices( 'error' );
            wc_clear_notices();
            $message = $errors ? wp_strip_all_tags( $errors[0]['notice'] ) : __( 'افزودن به سبد خرید با خطا مواجه شد.', 'negarin' );
            return new WP_Error( 'negarin_add_to_cart_failed', $message, array( 'status' => 400 ) );
        }

        return new WP_REST_Response(
            array(
                'success'    => true,
                'message'    => __( 'به سبد خرید اضافه شد.', 'negarin' ),
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'fragments'  => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
            ),
            200
        );
    }
}