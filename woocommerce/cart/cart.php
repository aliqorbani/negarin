<?php
/**
 * WooCommerce cart page override.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_cart' );
?>

<div class="max-w-7xl mx-auto px-4 py-8">

	<?php if ( WC()->cart->is_empty() ) : ?>

		<div class="text-center py-24">
			<p class="mb-6 opacity-70"><?php esc_html_e( 'سبد خرید شما خالی است.', 'negarin' ); ?></p>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn--solid">
				<?php esc_html_e( 'مشاهده محصولات', 'negarin' ); ?>
			</a>
		</div>

	<?php else : ?>

		<form class="woocommerce-cart-form grid grid-cols-1 md:grid-cols-[1fr_320px] gap-8 items-start" method="post" action="<?php echo esc_url( wc_get_cart_url() ); ?>">
			<?php do_action( 'woocommerce_before_cart_table' ); ?>

			<div class="order-1 overflow-x-auto">
				<table class="w-full text-sm text-right">
					<thead>
						<tr class="bg-negarin-cream text-xs">
							<th class="py-3 px-3 font-normal"><?php esc_html_e( 'محصول', 'negarin' ); ?></th>
							<th class="py-3 px-3 font-normal"><?php esc_html_e( 'تعداد', 'negarin' ); ?></th>
							<th class="py-3 px-3 font-normal"><?php esc_html_e( 'قیمت تک', 'negarin' ); ?></th>
							<th class="py-3 px-3 font-normal"></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
							$product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
							$permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

							if ( ! $product || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
								continue;
							}
							?>
							<tr class="border-b border-black/5">
								<td class="py-4 px-3">
									<div class="flex items-center gap-3">
										<?php if ( $permalink ) : ?>
											<a href="<?php echo esc_url( $permalink ); ?>" class="shrink-0 w-16 h-20 block overflow-hidden">
												<?php echo $product->get_image( 'negarin-grid-3', array( 'class' => 'w-full h-full object-cover' ) ); // phpcs:ignore ?>
											</a>
										<?php endif; ?>
										<div>
											<a href="<?php echo esc_url( $permalink ); ?>" class="block"><?php echo wp_kses_post( $product->get_name() ); ?></a>
											<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore ?>
										</div>
									</div>
								</td>
								<td class="py-4 px-3">
									<?php
									if ( $product->is_sold_individually() ) {
										echo '1';
									} else {
										get_template_part(
											'template-parts/components/quantity-stepper',
											null,
											array(
												'product'       => $product,
												'cart_item_key' => $cart_item_key,
												'quantity'      => $cart_item['quantity'],
											)
										);
									}
									?>
								</td>
								<td class="py-4 px-3 whitespace-nowrap">
									<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $product ), $cart_item, $cart_item_key ); // phpcs:ignore ?>
								</td>
								<td class="py-4 px-3">
									<?php
									echo apply_filters( // phpcs:ignore
										'woocommerce_cart_item_remove_link',
										sprintf(
											'<a href="%s" class="remove text-red-500" aria-label="%s" data-product_id="%s" data-cart_item_key="%s">%s</a>',
											esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
											esc_attr__( 'حذف از سبد خرید', 'negarin' ),
											esc_attr( $product->get_id() ),
											esc_attr( $cart_item_key ),
											'<span class="dashicons dashicons-trash"></span>'
										),
										$cart_item_key
									);
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<button type="submit" name="update_cart" value="<?php esc_attr_e( 'بروزرسانی سبد خرید', 'negarin' ); ?>">
					<?php esc_html_e( 'بروزرسانی سبد خرید', 'negarin' ); ?>
				</button>
				<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
			</div>

			<div class="order-2 md:sticky md:top-24 bg-negarin-cream p-6 text-right">
				<h2 class="font-serif text-lg mb-4"><?php esc_html_e( 'فاکتور شما', 'negarin' ); ?></h2>

				<div class="flex items-center justify-between text-sm py-2 border-t border-black/10">
					<span><?php wc_cart_totals_subtotal_html(); ?></span>
					<span class="opacity-70"><?php esc_html_e( 'قیمت این فاکتور:', 'negarin' ); ?></span>
				</div>
				<div class="flex items-center justify-between text-sm py-2 border-t border-black/10">
					<span><?php wc_cart_totals_order_total_html(); ?></span>
					<span class="opacity-70"><?php esc_html_e( 'مبلغ قابل پرداخت:', 'negarin' ); ?></span>
				</div>

				<div class="text-sm bg-white rounded-sm px-4 py-3 my-4 flex items-center gap-2">
					<span>🎁</span>
					<span><?php esc_html_e( 'ارسال رو مهمان نگارین هستید :)', 'negarin' ); ?></span>
				</div>

				<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn btn--solid w-full">
					<?php esc_html_e( 'تایید و ادامه', 'negarin' ); ?>
				</a>
			</div>

			<?php do_action( 'woocommerce_after_cart_table' ); ?>
		</form>

	<?php endif; ?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
