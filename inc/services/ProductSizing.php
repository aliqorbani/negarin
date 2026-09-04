<?php
/**
 * "انتخاب سایز" — real WooCommerce variable-product sizing.
 *
 * Every abaya is a Variable Product using one global attribute, "سایز"
 * (taxonomy `pa_size`, numeric terms 32..56). This class:
 *
 *  1. Provisions that attribute + its terms in code on `init`, so a fresh
 *     environment never needs a manual "add attribute" step in wp-admin —
 *     consistent with the project's constants/code-first conventions. This
 *     only ever inserts rows the first time they're missing; safe to run
 *     on every request.
 *  2. Provides `is_sized_product()` / `get_size_options()` so templates
 *     don't need to know taxonomy internals.
 *  3. Exposes POST /wp-json/negarin/v1/size-select/add-to-cart, the AJAX
 *     endpoint the size-select modal posts a chosen variation to. A
 *     dedicated endpoint (rather than WooCommerce's generic
 *     `?wc-ajax=add_to_cart`) keeps the request/response shape identical
 *     to Services/CustomOrder.php's own endpoint, so assets/js/toast.js
 *     only has to understand one JSON shape.
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
	private const STANDARD_SIZES = array( 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52, 54, 56 );

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
	 * every size term the product carries (in store order), whether each
	 * one currently has an in-stock, purchasable variation, and that
	 * variation's ID (0 when out of stock — the button is shown, struck
	 * through, but not selectable, matching the Figma export).
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

		$variations = $product->get_available_variations();
		// Map "size term slug" -> the first matching in-stock variation.
		$by_slug = array();
		foreach ( $variations as $variation ) {
			$slug = $variation['attributes'][ 'attribute_' . $taxonomy ] ?? '';
			if ( '' === $slug ) {
				continue;
			}
			// Prefer an in-stock entry if one exists for this slug; otherwise keep the first.
			if ( ! isset( $by_slug[ $slug ] ) || ( ! $by_slug[ $slug ]['is_in_stock'] && $variation['is_in_stock'] ) ) {
				$by_slug[ $slug ] = $variation;
			}
		}

		$options = array();
		foreach ( $terms as $term ) {
			$variation = $by_slug[ $term->slug ] ?? null;
			$options[] = array(
				'term_id'      => $term->term_id,
				'slug'         => $term->slug,
				'label'        => self::to_persian_digits( $term->name ),
				'variation_id' => $variation ? (int) $variation['variation_id'] : 0,
				'in_stock'     => (bool) $variation && (bool) $variation['is_in_stock'],
			);
		}

		return $options;
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

		$options = self::get_size_options( $product );
		$match   = null;

		foreach ( $options as $option ) {
			if ( $option['variation_id'] === $variation_id ) {
				$match = $option;
				break;
			}
		}

		if ( ! $match || ! $match['in_stock'] ) {
			return new WP_Error( 'negarin_size_unavailable', __( 'سایز انتخابی موجود نیست، لطفاً سایز دیگری را انتخاب کنید.', 'negarin' ), array( 'status' => 409 ) );
		}

		$variation_attributes = array(
			'attribute_' . self::taxonomy() => $match['slug'],
		);

        if ( ! WC()->cart ) {
            wc_load_cart();
        }

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
