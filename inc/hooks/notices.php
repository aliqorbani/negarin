<?php
/**
 * WooCommerce prints "added to cart" / validation / error notices inline,
 * wherever `woocommerce_output_all_notices` happens to be hooked on each
 * template (single product, cart, checkout, shop loop, my-account) — each
 * one occupying its own chunk of the page. Per the 2026-09 decision, every
 * one of those becomes a toast instead:
 *
 *  1. Remove the four default output points.
 *  2. Print all pending notices exactly once per page load, into a single
 *     hidden container right after <body> — a passive data source, never
 *     shown as a page section.
 *  3. Also expose that same markup as a `woocommerce_add_to_cart_fragments`
 *     entry, so native AJAX add-to-cart (the shop-loop buttons —
 *     inc/hooks/woocommerce.php) delivers fresh notices the same way.
 *  4. assets/js/toast.js reads the container on page load and after every
 *     fragment refresh, turns each notice into a toast, then empties it.
 *
 * Services/CustomOrder.php and Services/ProductSizing.php's own REST
 * endpoints don't rely on this — they return their message directly and
 * assets/js/{custom-order,size-select}.js toast it straight away. This
 * hidden-container path is the catch-all for everything else that still
 * calls wc_add_notice() the ordinary way (coupons, cart quantity updates,
 * checkout validation, a hard page-reload fallback if JS ever fails).
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

remove_action( 'woocommerce_before_single_product', 'woocommerce_output_all_notices', 10 );
remove_action( 'woocommerce_before_cart', 'woocommerce_output_all_notices', 10 );
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 );
remove_action( 'woocommerce_account_content', 'woocommerce_output_all_notices', 10 );

/**
 * Renders wc_print_notices() output wrapped in the hidden container
 * toast.js looks for. Shared by the wp_body_open hook (normal page loads)
 * and the add_to_cart_fragments filter (AJAX) so there's exactly one
 * markup shape for the JS to parse either way.
 */
function negarin_render_hidden_notices(): string {
	if ( ! function_exists( 'wc_get_notices' ) || ! wc_notice_count() ) {
		return '<div id="negarin-wc-notices" class="hidden" aria-hidden="true"></div>';
	}

	ob_start();
	wc_print_notices(); // Also clears the notice session, so it can't reappear on the next page load.
	$notices = ob_get_clean();

	return '<div id="negarin-wc-notices" class="hidden" aria-hidden="true">' . $notices . '</div>';
}

add_action(
	'wp_body_open',
	function () {
		echo negarin_render_hidden_notices(); // phpcs:ignore WordPress.Security.EscapeOutput -- wc_print_notices() output, already escaped by WooCommerce core.
	}
);

add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$fragments['#negarin-wc-notices'] = negarin_render_hidden_notices();
		return $fragments;
	}
);
