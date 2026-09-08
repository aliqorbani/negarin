<?php
/**
 * WooCommerce integration: strip default wrappers in favour of theme markup,
 * enable AJAX add-to-cart everywhere, expose a cart-drawer fragment.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Remove default WC page wrappers — templates/woocommerce.php provides its own.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
// Reposition breadcrumbs.
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
//add_action( 'negarin_content_top', 'woocommerce_breadcrumb', 20 );

// The coupon form defaults to the very top of the checkout page (above
// billing fields even). checkout/review-order.php and cart/cart.php both
// call wc_get_template('checkout/form-coupon.php') themselves instead,
// right above the product table, so it reads better next to the items
// it actually discounts.
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

/**
 * Per the Figma flow (Checkout → Payment Select are separate frames), the
 * product table is never repeated on checkout — only on the cart page.
 * The "فاکتور شما" sidebar's running total is checkout's only summary, so
 * step 2 (see woocommerce/checkout/form-checkout.php) is purely payment
 * -method selection, matching the "Payment Select" frame exactly — this
 * removes the item table WooCommerce would otherwise render above it.
 */
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );

// Ensure archive/shop grid uses our column count via a filter instead of a shortcode attribute.
add_filter(
    'loop_shop_columns',
    function () {
        return 4;
    }
);

add_filter(
    'woocommerce_add_to_cart_fragments',
    function ( $fragments ) {
        ob_start();
        get_template_part( 'template-parts/components/cart-drawer-count' );
        $fragments['.negarin-cart-count'] = ob_get_clean();
        return $fragments;
    }
);

add_filter(
    'woocommerce_add_to_cart_fragments',
    function ( $fragments ) {
        ob_start();
        get_template_part( 'template-parts/components/mini-cart-dropdown' );
        $fragments['#negarin-mini-cart'] = ob_get_clean();
        return $fragments;
    }
);

/**
 * cart.php builds its own complete order-summary sidebar ("فاکتور شما")
 * instead of WooCommerce's default cart-totals box — but that default box
 * (heading "Cart totals", coupon form, Total row, "Proceed to Checkout")
 * is still hooked to woocommerce_cart_collaterals / woocommerce_after_cart
 * by WooCommerce core, and cart.php still calls those hooks for other
 * legitimate default behavior (cross-sells, etc.), so it was rendering a
 * second, unstyled totals box underneath ours. Removing just that one
 * callback from wherever WooCommerce attaches it (defensive on all three
 * hooks it might use across versions — removing a callback that was never
 * actually attached to a given hook is a harmless no-op).
 */
add_action(
    'wp',
    function () {
        if ( ! is_cart() ) {
            return;
        }
        remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );
        remove_action( 'woocommerce_after_cart', 'woocommerce_cart_totals', 10 );
        remove_action( 'woocommerce_after_cart_table', 'woocommerce_cart_totals', 10 );
    }
);

add_filter(
    'woocommerce_update_order_review_fragments',
    function ( $fragments ) {
        ob_start();
        echo '<div id="negarin-order-totals">';
        get_template_part( 'template-parts/components/order-totals-rows' );
        echo '</div>';
        $fragments['#negarin-order-totals'] = ob_get_clean();
        return $fragments;
    }
);

/**
 * Force AJAX add-to-cart on archive/shop loops (single product page keeps
 * its own form since it has variations).
 */
add_filter(
    'woocommerce_loop_add_to_cart_args',
    function ( $args, $product ) {
        if ( $product->is_type( 'simple' ) && $product->is_purchasable() ) {
            $args['class'] = implode(
                ' ',
                array_filter(
                    array(
                        isset( $args['class'] ) ? $args['class'] : '',
                        'ajax_add_to_cart',
                        'add_to_cart_button',
                    )
                )
            );
        }
        return $args;
    },
    10,
    2
);