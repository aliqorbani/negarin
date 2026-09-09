<?php
/**
 * AJAX-ifies quantity changes on the cart page.
 *
 * The classic cart form (woocommerce/cart/cart.php) only recalculates
 * totals when its hidden `update_cart` submit button fires — a full,
 * page-reloading POST. assets/js/cart.js debounces the quantity
 * stepper's `change` event and calls this REST route instead, so a
 * shopper clicking +/- several times in a row (or typing a number
 * directly) never triggers more than one request, and never a page
 * reload.
 *
 * Mirrors WC_Form_Handler::update_cart_action() (WC()->cart->set_quantity()
 * followed by the same stock/backorder validation the classic cart page
 * runs via the `woocommerce_check_cart_items` action on every load) —
 * just returned as JSON instead of a redirect.
 *
 * A quantity that exceeds available stock is *not* treated as a request
 * failure: WooCommerce itself doesn't revert the quantity in that case,
 * only flags it with a notice, so the response still comes back 200 with
 * fresh fragments and `success: false` — the sidebar stays in sync with
 * whatever actually landed in the cart, and assets/js/cart.js toasts the
 * notice as an error either way.
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

class CartAjax {

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes(): void {
        register_rest_route(
            'negarin/v1',
            '/cart/update-item',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_update_item' ),
                'permission_callback' => '__return_true', // Same as add-to-cart: cart state lives in the WC session, not behind a capability check.
                'args'                => array(
                    'cart_item_key' => array( 'required' => true ),
                    'quantity'      => array( 'required' => true ),
                ),
            )
        );
    }

    public function handle_update_item( WP_REST_Request $request ) {
        if ( ! WC()->cart ) {
            wc_load_cart();
        }

        $cart_item_key = sanitize_text_field( (string) $request->get_param( 'cart_item_key' ) );
        $quantity      = absint( $request->get_param( 'quantity' ) );

        if ( ! WC()->cart->get_cart_item( $cart_item_key ) ) {
            return new WP_Error( 'negarin_invalid_cart_item', __( 'این کالا در سبد خرید یافت نشد.', 'negarin' ), array( 'status' => 404 ) );
        }

        if ( $quantity < 1 ) {
            return new WP_Error( 'negarin_invalid_quantity', __( 'تعداد وارد شده نامعتبر است.', 'negarin' ), array( 'status' => 422 ) );
        }

        WC()->cart->set_quantity( $cart_item_key, $quantity, true );
        WC()->cart->check_cart_items(); // Stock/backorder validation — set_quantity() alone doesn't run it.

        $errors = wc_get_notices( 'error' );

        return new WP_REST_Response(
            array(
                'success'   => empty( $errors ),
                // Fragments (mini-cart, cart count, order totals, and the
                // hidden notices container this same call just populated)
                // stay in sync with the cart either way — see class docblock.
                'message'   => $errors ? wp_strip_all_tags( $errors[0]['notice'] ) : __( 'سبد خرید بروزرسانی شد.', 'negarin' ),
                'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
            ),
            200
        );
    }
}
