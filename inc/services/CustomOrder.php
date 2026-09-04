<?php
/**
 * "سفارش شخصی" (custom measurement order) — the fallback offered from
 * inside the size-select modal (template-parts/components/size-select-modal.php)
 * when none of a product's standard sizes fit.
 *
 * Flow:
 *  1. From the size-select modal, "سفارش شخصی" opens this modal
 *     (template-parts/components/custom-order-modal.php), stacked on top —
 *     exactly like the size-chart modal stacks on the size-select modal.
 *  2. The form always collects exactly four measurements: دور سینه، قد
 *     آستین، سرشانه، قد عبا (fixed on purpose — not admin-configurable,
 *     per the 2026-09 decision to match the simplified Figma form exactly).
 *  3. This requires a logged-in customer — OTP is the only identity this
 *     store has, and there's no separate guest name/phone step anymore.
 *     The "سفارش شخصی" trigger sends a signed-out shopper to /my-account/
 *     (?redirect_to=<this product>) instead of opening the modal; the
 *     modal itself and this endpoint both re-check login as a hard guard.
 *  4. On submit, POST /wp-json/negarin/v1/custom-order/add-to-cart adds
 *     the *parent* product to the cart with no variation — a custom order
 *     is made-to-measure, not picked from stocked sizes, so no variation
 *     stock applies. The measurements ride along as cart_item_data, which
 *     WooCommerce copies onto the **order item meta** at checkout.
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

class CustomOrder {

	/**
	 * Fixed field definitions: key => [label, unit]. Not admin-configurable —
	 * see the class docblock for why.
	 */
	private const FIELDS = array(
		'chest'    => array( 'label' => 'دور سینه', 'unit' => 'سانتی‌متر' ),
		'sleeve'   => array( 'label' => 'قد آستین', 'unit' => 'سانتی‌متر' ),
		'shoulder' => array( 'label' => 'سرشانه', 'unit' => 'سانتی‌متر' ),
		'length'   => array( 'label' => 'قد عبا', 'unit' => 'سانتی‌متر' ),
	);

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_filter( 'woocommerce_get_item_data', array( $this, 'display_in_cart' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_to_order_item' ), 10, 4 );
	}

	/**
	 * @return array<int, array{key:string, label:string, unit:string}>
	 */
	public static function get_measurement_fields(): array {
		$fields = array();
		foreach ( self::FIELDS as $key => $field ) {
			$fields[] = array(
				'key'   => $key,
				'label' => $field['label'],
				'unit'  => $field['unit'],
			);
		}
		return $fields;
	}

	/**
	 * A product is eligible for the custom-order fallback whenever it's
	 * offered through the sized/variable flow — a plain simple product has
	 * nothing to "not fit", so it never shows this option.
	 */
	public static function is_available_for( \WC_Product $product ): bool {
		return $product->is_type( 'variable' );
	}

	/**
	 * Validate and normalize the raw measurement payload. Returns WP_Error
	 * on any failure, including "not logged in" — that check lives here
	 * too (not just in the REST route) so it's enforced no matter how this
	 * method is ever called.
	 *
	 * @param array $raw Decoded JSON body: { measurements: { key: value } }.
	 * @return array|\WP_Error
	 */
	public function validate_submission( array $raw ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'negarin_login_required', __( 'برای ثبت سفارش شخصی ابتدا وارد حساب کاربری خود شوید.', 'negarin' ), array( 'status' => 401 ) );
		}

		$measurements = array();
		$errors       = array();
		$raw_values   = is_array( $raw['measurements'] ?? null ) ? $raw['measurements'] : array();

		foreach ( self::FIELDS as $key => $field ) {
			$value = trim( (string) ( $raw_values[ $key ] ?? '' ) );

			if ( '' === $value ) {
				$errors[] = sprintf(
					/* translators: %s: measurement field label, e.g. "دور سینه" */
					__( 'لطفاً %s را وارد کنید.', 'negarin' ),
					$field['label']
				);
				continue;
			}

			if ( ! is_numeric( $value ) || $value <= 0 || $value > 300 ) {
				$errors[] = sprintf(
					/* translators: %s: measurement field label */
					__( 'مقدار وارد شده برای %s نامعتبر است.', 'negarin' ),
					$field['label']
				);
				continue;
			}

			$measurements[ $key ] = round( (float) $value, 1 );
		}

		if ( $errors ) {
			return new WP_Error( 'negarin_invalid_measurements', implode( ' ', $errors ), array( 'status' => 422 ) );
		}

		return array( 'measurements' => $measurements );
	}

	public function register_routes(): void {
		register_rest_route(
			'negarin/v1',
			'/custom-order/add-to-cart',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_add_to_cart' ),
				'permission_callback' => '__return_true', // validate_submission() enforces the login requirement itself.
				'args'                => array(
					'product_id'   => array( 'required' => true ),
					'measurements' => array( 'required' => true ),
				),
			)
		);
	}

	public function handle_add_to_cart( WP_REST_Request $request ) {
		$product_id = absint( $request->get_param( 'product_id' ) );
		$product    = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_purchasable() ) {
			return new WP_Error( 'negarin_invalid_product', __( 'محصول یافت نشد.', 'negarin' ), array( 'status' => 404 ) );
		}

		$submission = $this->validate_submission( (array) $request->get_json_params() );

		if ( is_wp_error( $submission ) ) {
			return $submission;
		}

		$cart_item_data = array(
			'negarin_custom_order' => $submission,
			'unique_key'           => md5( microtime() . wp_rand() ),
		);

		// No variation_id: a custom order is made-to-measure, not pulled
		// from a stocked size, so none of the product's variations apply.
		$cart_item_key = WC()->cart->add_to_cart( $product_id, 1, 0, array(), $cart_item_data );

		if ( ! $cart_item_key ) {
			$errors = wc_get_notices( 'error' );
			wc_clear_notices();
			$message = $errors ? wp_strip_all_tags( $errors[0]['notice'] ) : __( 'افزودن به سبد خرید با خطا مواجه شد.', 'negarin' );
			return new WP_Error( 'negarin_add_to_cart_failed', $message, array( 'status' => 400 ) );
		}

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => __( 'سفارش شخصی به سبد خرید اضافه شد.', 'negarin' ),
				'cart_count' => WC()->cart->get_cart_contents_count(),
				'fragments'  => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
			),
			200
		);
	}

	/**
	 * Show a readable summary of the measurements in cart/checkout review tables.
	 */
	public function display_in_cart( array $item_data, array $cart_item ): array {
		if ( empty( $cart_item['negarin_custom_order'] ) ) {
			return $item_data;
		}

		$labels = wp_list_pluck( self::get_measurement_fields(), 'label', 'key' );

		foreach ( $cart_item['negarin_custom_order']['measurements'] as $key => $value ) {
			$item_data[] = array(
				'name'  => $labels[ $key ] ?? $key,
				'value' => $value,
			);
		}

		return $item_data;
	}

	/**
	 * Persist the measurements as order item meta — visible on the order
	 * edit screen and in order emails.
	 */
	public function save_to_order_item( $item, $cart_item_key, $values, $order ): void {
		if ( empty( $values['negarin_custom_order'] ) ) {
			return;
		}

		$labels = wp_list_pluck( self::get_measurement_fields(), 'label', 'key' );

		foreach ( $values['negarin_custom_order']['measurements'] as $key => $value ) {
			$item->add_meta_data( $labels[ $key ] ?? $key, $value, true );
		}
	}
}
