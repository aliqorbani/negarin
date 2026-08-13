<?php
/**
 * Tiny cart-count fragment, refreshed via WooCommerce AJAX cart fragments.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo class_exists( 'WooCommerce' ) ? (int) WC()->cart->get_cart_contents_count() : 0;
