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