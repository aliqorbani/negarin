<?php
/**
 * Checkout field customization: Persian labels matching the Figma export,
 * two extra address fields (پلاک / واحد) stored as standard WooCommerce
 * order meta, and "ship to a different address" disabled since the
 * design only collects one address.
 *
 * @package Negarin
 */

namespace Negarin\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CheckoutFields {

	public function __construct() {
		add_filter( 'woocommerce_checkout_fields', array( $this, 'customize_fields' ) );
		add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );
		add_filter( 'woocommerce_order_button_text', array( $this, 'order_button_text' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_extra_address_fields' ) );
	}

	/**
	 * WooCommerce only knows how to auto-save its own core address props;
	 * پلاک/واحد are custom, so we persist them explicitly.
	 */
	public function save_extra_address_fields( int $order_id ): void {
		$order = wc_get_order( $order_id );

		if ( ! empty( $_POST['billing_plaque'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$order->update_meta_data( '_billing_plaque', sanitize_text_field( wp_unslash( $_POST['billing_plaque'] ) ) ); // phpcs:ignore
		}
		if ( ! empty( $_POST['billing_unit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$order->update_meta_data( '_billing_unit', sanitize_text_field( wp_unslash( $_POST['billing_unit'] ) ) ); // phpcs:ignore
		}

		$order->save();
	}

	public function customize_fields( array $fields ): array {
		// Company/email/phone-in-billing-block aren't in the design; keep
		// phone (needed for delivery) but drop company. Email stays required
		// by WooCommerce core for guest order lookups.
		unset( $fields['billing']['billing_company'] );

		$fields['billing']['billing_first_name']['label']       = __( 'نام', 'negarin' );
		$fields['billing']['billing_first_name']['priority']    = 10;
		$fields['billing']['billing_last_name']['label']        = __( 'نام خانوادگی', 'negarin' );
		$fields['billing']['billing_last_name']['priority']     = 20;

		$fields['billing']['billing_state']['label']    = __( 'استان', 'negarin' );
		$fields['billing']['billing_state']['priority'] = 30;
		$fields['billing']['billing_city']['label']     = __( 'شهر', 'negarin' );
		$fields['billing']['billing_city']['priority']  = 40;

		$fields['billing']['billing_address_1']['label']       = __( 'آدرس', 'negarin' );
		$fields['billing']['billing_address_1']['priority']    = 50;
		$fields['billing']['billing_address_1']['class']       = array( 'form-row-wide' );

		unset( $fields['billing']['billing_address_2'] );

		$fields['billing']['billing_plaque'] = array(
			'label'    => __( 'پلاک', 'negarin' ),
			'required' => true,
			'class'    => array( 'form-row-first' ),
			'priority' => 60,
		);

		$fields['billing']['billing_unit'] = array(
			'label'    => __( 'واحد', 'negarin' ),
			'required' => false,
			'class'    => array( 'form-row-last' ),
			'priority' => 70,
		);

		$fields['billing']['billing_postcode']['label']       = __( 'کدپستی', 'negarin' );
		$fields['billing']['billing_postcode']['priority']    = 80;
		$fields['billing']['billing_postcode']['class']       = array( 'form-row-wide' );

		$fields['billing']['billing_phone']['priority'] = 90;
		$fields['billing']['billing_email']['priority'] = 100;

		return $fields;
	}

	public function order_button_text( string $text ): string {
		return __( 'تایید و ادامه', 'negarin' );
	}
}
