<?php
/**
 * Mobile cart-page product card — Figma "Cart_mobile" (node 165:422).
 *
 * Below the `md` breakpoint this replaces the classic <tr> rendered in
 * woocommerce/cart/cart.php; both read from the exact same cart item, they're
 * just two presentations of it swapped via `hidden`/`md:hidden` so there is
 * still only one loop over WC()->cart->get_cart() worth of *logic* to keep in
 * sync (the filters below mirror cart.php's <tr> one-for-one).
 *
 * Expected $args: [ 'cart_item' => array, 'cart_item_key' => string ]
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart_item     = $args['cart_item'] ?? null;
$cart_item_key = $args['cart_item_key'] ?? '';

if ( ! $cart_item ) {
	return;
}

$product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

if ( ! $product || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
	return;
}

$permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
?>
<div class="flex gap-3 border-t border-negarin-line pt-4 pb-4">
	<?php if ( $permalink ) : ?>
		<a href="<?php echo esc_url( $permalink ); ?>" class="shrink-0 w-[74px] h-[115px] block overflow-hidden">
			<?php echo $product->get_image( 'thumbnail', array( 'class' => 'm-0 w-full h-full object-cover' ) ); // phpcs:ignore ?>
		</a>
	<?php endif; ?>

	<div class="flex-1 min-w-0 flex flex-col justify-between">
		<div>
			<div class="flex items-start justify-between gap-2">
				<a href="<?php echo esc_url( $permalink ); ?>" class="font-serif font-semibold text-lg text-negarin-gray">
					<?php echo wp_kses_post( $product->get_name() ); ?>
				</a>
				<span class="font-serif font-semibold text-lg text-negarin-gray whitespace-nowrap">
					<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $product ), $cart_item, $cart_item_key ); // phpcs:ignore ?>
				</span>
			</div>
			<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore ?>
		</div>

		<div class="flex items-center justify-between">
			<?php
			if ( $product->is_sold_individually() ) {
				echo '<span class="text-sm opacity-70">' . esc_html__( 'تعداد: ۱', 'negarin' ) . '</span>'; // phpcs:ignore
			} else {
                $args = array(
                        'product'       => $product,
                        'cart_item_key' => $cart_item_key,
                        'variant'       => 'compact',
                        'quantity'      => $cart_item['quantity'],
                );

                get_template_part(
                        'template-parts/components/quantity-stepper',
                        null,
                        $args
                );
			}
			?>

			<?php
			echo apply_filters( // phpcs:ignore
				'woocommerce_cart_item_remove_link',
				sprintf(
					'<a href="%s" class="text-negarin-red shrink-0" aria-label="%s" data-product_id="%s" data-cart_item_key="%s">%s</a>',
					esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
					esc_attr__( 'حذف از سبد خرید', 'negarin' ),
					esc_attr( $product->get_id() ),
					esc_attr( $cart_item_key ),
					'<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6m2 0v13a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V6h10ZM10 10.5v6M14 10.5v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
				),
				$cart_item_key
			);
			?>
		</div>
	</div>
</div>
