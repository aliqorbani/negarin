<?php
/**
 * "سفارش شخصی" (custom measurement order) feature.
 *
 * Flow:
 *  1. Product page shows a "سفارش شخصی" button next to (or instead of) the
 *     normal add-to-cart button for products that opt in (`negarin_custom_order`
 *     product checkbox meta — see register_product_field()).
 *  2. Clicking it opens a modal (template-parts/components/custom-order-modal.php)
 *     built from the admin-managed `measurement_fields` in Theme Options ->
 *     Sizing Presets. Each field is a dropdown of presets that can be
 *     switched to manual numeric entry.
 *  3. If the shopper isn't logged in, the same modal also collects name +
 *     phone (no separate account step — this reuses the OTP identity once
 *     they proceed to checkout, it does not create an account itself).
 *  4. On submit, the values are added to the WooCommerce cart item as
 *     `cart_item_data`, which WooCommerce automatically copies to
 *     **order item meta** on checkout — exactly where you asked for it to live.
 *
 * @package Negarin
 */

namespace Negarin\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CustomOrder {

	/**
	 * Meta key (on the product) that marks it as eligible for custom sizing.
	 */
	private const PRODUCT_FLAG = '_negarin_custom_order';

	public function __construct() {
		add_action( 'acf/init', array( $this, 'register_product_field' ) );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_before_add' ), 10, 3 );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'attach_cart_item_data' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'display_in_cart' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_to_order_item' ), 10, 4 );
	}

	/**
	 * Small ACF checkbox on the Product edit screen so any product can opt
	 * into custom sizing without a developer touching code.
	 */
	public function register_product_field(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_negarin_custom_order',
				'title'    => __( 'Custom Order (Sizing)', 'negarin' ),
				'fields'   => array(
					array(
						'key'   => 'field_negarin_custom_order_enabled',
						'name'  => 'custom_order_enabled',
						'label' => __( 'Allow custom sizing for this product', 'negarin' ),
						'type'  => 'true_false',
						'ui'    => 1,
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
			)
		);
	}

	public static function is_enabled_for( int $product_id ): bool {
		return (bool) get_field( 'custom_order_enabled', $product_id );
	}

	/**
	 * Return the admin-configured measurement field definitions, decoded
	 * into a simple array the modal template can loop over.
	 */
	public static function get_measurement_fields(): array {
		$rows = negarin_option( 'measurement_fields', array() );

		if ( ! is_array( $rows ) ) {
			return array();
		}

		return array_map(
			static function ( $row ) {
				return array(
					'key'     => sanitize_key( $row['key'] ?? '' ),
					'label'   => $row['label'] ?? '',
					'unit'    => $row['unit'] ?? '',
					'presets' => array_map(
						static fn( $p ) => array(
							'label' => $p['label'] ?? '',
							'value' => $p['value'] ?? '',
						),
						is_array( $row['presets'] ?? null ) ? $row['presets'] : array()
					),
				);
			},
			$rows
		);
	}

	/**
	 * Validate and normalize the raw POSTed measurement + contact values
	 * before they ever touch the cart. Returns WP_Error on failure.
	 *
	 * @param array $raw Raw $_POST-shaped array: measurements[key]=value, name, phone.
	 * @return array|\WP_Error
	 */
	public function validate_submission( array $raw ) {
		$fields       = self::get_measurement_fields();
		$measurements = array();
		$errors       = array();

		foreach ( $fields as $field ) {
			$value = trim( (string) ( $raw['measurements'][ $field['key'] ] ?? '' ) );

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

			$measurements[ $field['key'] ] = round( (float) $value, 1 );
		}

		if ( $errors ) {
			return new \WP_Error( 'negarin_invalid_measurements', implode( ' ', $errors ), array( 'status' => 422 ) );
		}

		$result = array( 'measurements' => $measurements );

		// Guest contact info is only required when nobody is logged in.
		if ( ! is_user_logged_in() ) {
			$name  = sanitize_text_field( $raw['name'] ?? '' );
			$phone = preg_replace( '/\D/', '', (string) ( $raw['phone'] ?? '' ) );

			if ( '' === $name ) {
				return new \WP_Error( 'negarin_missing_name', __( 'لطفاً نام خود را وارد کنید.', 'negarin' ), array( 'status' => 422 ) );
			}
			if ( ! preg_match( '/^09\d{9}$/', $phone ) ) {
				return new \WP_Error( 'negarin_missing_phone', __( 'شماره موبایل معتبر وارد کنید.', 'negarin' ), array( 'status' => 422 ) );
			}

			$result['guest_name']  = $name;
			$result['guest_phone'] = $phone;
		}

		$result['note'] = sanitize_textarea_field( $raw['note'] ?? '' );

		return $result;
	}

	/**
	 * Blocks WC_Cart::add_to_cart() entirely when a custom-order product's
	 * measurement submission is invalid or missing. Runs *after*
	 * `woocommerce_add_cart_item_data` in WooCommerce's own code, so the
	 * notice added there is preserved and the item is never actually added.
	 */
	public function validate_before_add( bool $passed, int $product_id, int $quantity ): bool {
		if ( ! self::is_enabled_for( $product_id ) ) {
			return $passed;
		}

		if ( empty( $_POST['negarin_custom_order_nonce'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['negarin_custom_order_nonce'] ) ), 'negarin_custom_order' ) ) {
			wc_add_notice( __( 'لطفاً فرم سفارش شخصی را از طریق دکمه مربوطه تکمیل کنید.', 'negarin' ), 'error' );
			return false;
		}

		$submission = $this->validate_submission( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( is_wp_error( $submission ) ) {
			wc_add_notice( $submission->get_error_message(), 'error' );
			return false;
		}

		return $passed;
	}

	/**
	 * Attach validated measurement data to the cart item — this is what
	 * WooCommerce later copies onto the order line item automatically.
	 * (Validity was already confirmed by validate_before_add(); this method
	 * re-validates defensively rather than trusting shared state across hooks.)
	 */
	public function attach_cart_item_data( array $cart_item_data, int $product_id, int $variation_id ) {
		if ( ! self::is_enabled_for( $product_id ) ) {
			return $cart_item_data;
		}

		$submission = $this->validate_submission( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( is_wp_error( $submission ) ) {
			return $cart_item_data;
		}

		$cart_item_data['negarin_custom_order'] = $submission;
		$cart_item_data['unique_key']            = md5( microtime() . wp_rand() );

		return $cart_item_data;
	}

	/**
	 * Show a readable summary of the measurements in cart/checkout review tables.
	 */
	public function display_in_cart( array $item_data, array $cart_item ): array {
		if ( empty( $cart_item['negarin_custom_order'] ) ) {
			return $item_data;
		}

		$fields = self::get_measurement_fields();
		$labels = wp_list_pluck( $fields, 'label', 'key' );

		foreach ( $cart_item['negarin_custom_order']['measurements'] as $key => $value ) {
			$item_data[] = array(
				'name'  => $labels[ $key ] ?? $key,
				'value' => $value,
			);
		}

		return $item_data;
	}

	/**
	 * Persist the measurements as order item meta — exactly where you asked
	 * for this to be stored (visible on the order edit screen and in emails).
	 */
	public function save_to_order_item( $item, $cart_item_key, $values, $order ): void {
		if ( empty( $values['negarin_custom_order'] ) ) {
			return;
		}

		$data = $values['negarin_custom_order'];

		foreach ( $data['measurements'] as $key => $value ) {
			$fields = self::get_measurement_fields();
			$labels = wp_list_pluck( $fields, 'label', 'key' );
			$item->add_meta_data( $labels[ $key ] ?? $key, $value, true );
		}

		if ( ! empty( $data['note'] ) ) {
			$item->add_meta_data( __( 'توضیحات مشتری', 'negarin' ), $data['note'], true );
		}

		if ( ! empty( $data['guest_name'] ) ) {
			$item->add_meta_data( __( 'نام (مهمان)', 'negarin' ), $data['guest_name'], true );
			$order->set_billing_first_name( $data['guest_name'] );
		}

		if ( ! empty( $data['guest_phone'] ) ) {
			$item->add_meta_data( __( 'موبایل (مهمان)', 'negarin' ), $data['guest_phone'], true );
			$order->set_billing_phone( $data['guest_phone'] );
		}
	}
}
