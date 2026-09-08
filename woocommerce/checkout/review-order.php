<?php
/**
 * Checkout order-review table override. Matches cart.php's table styling.
 *
 * Deliberately has NO totals <tfoot> (subtotal/coupon-discount/shipping/
 * tax/total) — those all live in the "فاکتور شما" sidebar in
 * checkout/form-checkout.php instead, so there's exactly one place on the
 * page showing the amount due, not two (see the 2026-09 fix where the
 * cart page had this same duplicate-totals problem).
 *
 * The coupon form isn't here either — it has its own <form> tag, which
 * can't be nested inside the outer <form name="checkout"> (browsers
 * silently drop nested forms, which would break its AJAX submit handler
 * entirely). It's called from form-checkout.php, before that outer form
 * opens.
 *
 * checkout/payment.php renders immediately after this (both are hooked to
 * woocommerce_checkout_order_review, priorities 10 and 20 — see
 * WooCommerce core's wc-template-hooks.php).
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="overflow-x-auto border border-negarin-line">
	<table class="w-full text-sm text-right woocommerce-checkout-review-order-table">
		<thead>
			<tr class="bg-[#f0f1f2] text-xs">
				<th class="py-3 px-3 font-normal"><?php esc_html_e( 'محصول', 'negarin' ); ?></th>
				<th class="py-3 px-3 font-normal"><?php esc_html_e( 'تعداد', 'negarin' ); ?></th>
				<th class="py-3 px-3 font-normal"><?php esc_html_e( 'قیمت', 'negarin' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php do_action( 'woocommerce_review_order_before_cart_contents' ); ?>

			<?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) : ?>
				<?php
				$product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$visible = apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key );

				if ( ! $product instanceof WC_Product || ! $product->exists() || $cart_item['quantity'] <= 0 || ! $visible ) {
					continue;
				}

				$permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				?>
				<tr class="border-b border-black/5 <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
					<td class="py-4 px-3">
						<div class="flex items-center gap-3">
							<?php if ( $permalink ) : ?>
								<a href="<?php echo esc_url( $permalink ); ?>" class="shrink-0 w-14 h-18 block overflow-hidden">
									<?php echo $product->get_image( 'negarin-grid-3', array( 'class' => 'w-full h-full object-cover' ) ); // phpcs:ignore ?>
								</a>
							<?php endif; ?>
							<div>
								<a href="<?php echo esc_url( $permalink ); ?>" class="block"><?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $product->get_name(), $cart_item, $cart_item_key ) ); ?></a>
								<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore ?>
							</div>
						</div>
					</td>
					<td class="py-4 px-3">
						&times; <?php echo esc_html( $cart_item['quantity'] ); ?>
					</td>
					<td class="py-4 px-3 whitespace-nowrap">
						<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore ?>
					</td>
				</tr>
			<?php endforeach; ?>

			<?php do_action( 'woocommerce_review_order_after_cart_contents' ); ?>
		</tbody>
	</table>
</div>
