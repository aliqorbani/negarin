<?php
/**
 * Quantity stepper (−  N  +) that wraps WooCommerce's own quantity <input>
 * so it stays 100% compatible with `woocommerce_quantity_input` filters,
 * min/max/step rules, and the classic cart's "update_cart" submit flow —
 * we only add the +/- buttons and auto-submit on change.
 *
 * Expected $args: [ 'product' => WC_Product, 'cart_item_key' => string, 'quantity' => int ]
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product       = $args['product'] ?? null;
$cart_item_key = $args['cart_item_key'] ?? '';
$quantity      = $args['quantity'] ?? 1;

if ( ! $product ) {
	return;
}
?>
<div class="negarin-qty-stepper flex items-center gap-2" x-data="{ qty: <?php echo (int) $quantity; ?> }">
	<button
		type="button"
		class="w-8 h-8 border border-black/15 rounded-sm flex items-center justify-center"
		@click="qty = Math.max(1, qty - 1); let el = $el.parentElement.querySelector('.negarin-qty-input'); el.value = qty; el.dispatchEvent(new Event('change', { bubbles: true }))"
		aria-label="<?php esc_attr_e( 'کاهش تعداد', 'negarin' ); ?>"
	>−</button>

	<?php
	woocommerce_quantity_input(
		array(
			'input_name'  => "cart[{$cart_item_key}][qty]",
			'input_value' => $quantity,
			'classes'     => array( 'w-12', 'text-center', 'border-0', 'negarin-qty-input' ),
		),
		$product
	);
	?>

	<button
		type="button"
		class="w-8 h-8 border border-black/15 rounded-sm flex items-center justify-center"
		@click="qty = qty + 1; let el = $el.parentElement.querySelector('.negarin-qty-input'); el.value = qty; el.dispatchEvent(new Event('change', { bubbles: true }))"
		aria-label="<?php esc_attr_e( 'افزایش تعداد', 'negarin' ); ?>"
	>+</button>
</div>
