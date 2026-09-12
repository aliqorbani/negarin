<?php
/**
 * Quantity stepper (−  N  +) that wraps WooCommerce's own quantity <input>
 * so it stays 100% compatible with `woocommerce_quantity_input` filters,
 * min/max/step rules, and the classic cart's "update_cart" submit flow —
 * we only add the +/- buttons and auto-submit on change.
 *
 * Expected $args: [
 *   'product'       => WC_Product,
 *   'cart_item_key' => string,
 *   'quantity'      => int,
 *   'variant'       => 'default' (gapped buttons, desktop table) | 'compact'
 *                       (single bordered box, Figma 165:422 mobile cart card),
 * ]
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product       = $args['product'] ?? null;
$cart_item_key = $args['cart_item_key'] ?? '';
$quantity      = $args['quantity'] ?? 1;
$is_compact    = 'compact' === ( $args['variant'] ?? 'default' );

if ( ! $product ) {
	return;
}
?>
<div
	class="negarin-qty-stepper inline-flex items-center <?php echo $is_compact ? 'h-[42px] border border-negarin-line divide-x divide-negarin-line' : 'gap-2'; ?>"
	<?php echo $is_compact ? 'dir="ltr"' : ''; ?>
	x-data="{ qty: <?php echo (int) $quantity; ?> }"
>
	<button
		type="button"
		class="<?php echo $is_compact ? 'w-[25px] h-full shrink-0 flex items-center justify-center' : 'w-8 h-8 border border-black/15 rounded-sm flex items-center justify-center'; ?>"
		@click="qty = Math.max(1, qty - 1); let el = $el.parentElement.querySelector('.negarin-qty-input'); el.value = qty; el.dispatchEvent(new Event('change', { bubbles: true }))"
		aria-label="<?php esc_attr_e( 'کاهش تعداد', 'negarin' ); ?>"
	>−</button>

	<?php
	woocommerce_quantity_input(
		array(
			'input_name'  => "cart[{$cart_item_key}][qty]",
			'input_value' => $quantity,
			'classes'     => $is_compact
				? array( 'w-[30px]', 'h-full', 'shrink-0', 'text-center', 'border-0', 'negarin-qty-input','px-0' )
				: array( 'w-12', 'text-center', 'border-0', 'negarin-qty-input' ),
		),
		$product
	);
	?>

	<button
		type="button"
		class="<?php echo $is_compact ? 'w-[25px] h-full shrink-0 flex items-center justify-center' : 'w-8 h-8 border border-black/15 rounded-sm flex items-center justify-center'; ?>"
		@click="qty = qty + 1; let el = $el.parentElement.querySelector('.negarin-qty-input'); el.value = qty; el.dispatchEvent(new Event('change', { bubbles: true }))"
		aria-label="<?php esc_attr_e( 'افزایش تعداد', 'negarin' ); ?>"
	>+</button>
</div>
