<?php
/**
 * Cart-count badge — refreshed via WooCommerce AJAX cart fragments
 * (inc/hooks/woocommerce.php, `.negarin-cart-count` selector).
 *
 * Always renders the wrapper itself (hidden via CSS when the count is 0)
 * rather than being conditionally included by the caller — outerHTML
 * fragment replacement can only update a node that already exists in the
 * page, so if this element weren't in the initial HTML on an empty cart,
 * the very first add-to-cart would have nothing to replace and the badge
 * would silently fail to appear until the next full page load.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$negarin_cart_count = class_exists( 'WooCommerce' ) ? (int) WC()->cart->get_cart_contents_count() : 0;
?>
<span class="negarin-cart-count absolute -top-2 -left-2 text-[10px] bg-negarin-ink text-white rounded-full w-4 h-4 flex items-center justify-center<?php echo $negarin_cart_count > 0 ? '' : ' hidden'; ?>">
	<?php echo esc_html( $negarin_cart_count ); ?>
</span>