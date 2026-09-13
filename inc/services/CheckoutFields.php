<?php
/**
 * Checkout field customization: Persian labels matching the Figma export,
 * two extra address fields (پلاک / واحد) stored as standard WooCommerce
 * order meta, and "ship to a different address" disabled since the
 * design only collects one address.
 *
 * The checkout form never asks for a phone number or email — the phone is
 * already known from OTP login and no email is collected anywhere on the
 * site. Both are set programmatically on the order in
 * save_extra_address_fields() instead of being rendered as form fields.
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

        $phone = $this->resolve_billing_phone();

        if ( $phone ) {
            $order->set_billing_phone( $phone );
        }

        $order->set_billing_email( $this->resolve_billing_email( $phone ) );

        $order->save();
    }

    /**
     * The phone is never asked for at checkout — OtpAuth already stores it
     * on the user account when the customer logs in.
     */
    private function resolve_billing_phone(): string {
        if ( ! is_user_logged_in() ) {
            return '';
        }

        $user_id = get_current_user_id();
        $phone   = get_user_meta( $user_id, 'billing_phone', true );

        return $phone ?: (string) get_user_meta( $user_id, 'negarin_phone', true );
    }

    /**
     * No email is ever collected on the site. WooCommerce still expects one
     * on every order (admin display, order emails, lookups), so we generate
     * a stable, deterministic address from the phone number. If the account
     * somehow already has a real email (e.g. set manually from wp-admin),
     * that takes priority.
     */
    private function resolve_billing_email( string $phone ): string {
        if ( is_user_logged_in() ) {
            $user_email = wp_get_current_user()->user_email;

            if ( $user_email ) {
                return $user_email;
            }
        }

        $digits = preg_replace( '/\D+/', '', $phone );

        return ( $digits ?: 'guest' ) . '@' . $this->fallback_email_domain();
    }

    private function fallback_email_domain(): string {
        return defined( 'NEGARIN_FALLBACK_EMAIL_DOMAIN' ) ? NEGARIN_FALLBACK_EMAIL_DOMAIN : 'negarin.local';
    }

    public function customize_fields( array $fields ): array {
        // None of these are in the design. Phone/email are set
        // programmatically after order creation — see save_extra_address_fields().
        unset( $fields['billing']['billing_company'] );
        unset( $fields['billing']['billing_phone'] );
        unset( $fields['billing']['billing_email'] );

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

        return $fields;
    }

    public function order_button_text( string $text ): string {
        return __( 'تایید و ادامه', 'negarin' );
    }
}