<?php
/**
 * The rows inside the "فاکتور شما" box — included by both cart/cart.php
 * and checkout/form-checkout.php so there's exactly one totals
 * implementation to keep in sync. Mirrors the same conditional rows
 * WooCommerce's default checkout/review-order.php <tfoot> shows
 * (coupon discount, shipping, fees, tax) — just styled into this sidebar
 * instead of living in its own separate box.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! WC()->cart ) {
	return;
}
?>
<div class="flex items-center justify-between text-sm py-2 border-t border-black/10">
	<span class="opacity-70"><?php esc_html_e( 'قیمت این فاکتور:', 'negarin' ); ?></span>
	<span><?php wc_cart_totals_subtotal_html(); ?></span>
</div>

<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
	<div class="flex items-center justify-between text-sm py-2 border-t border-black/10">
		<span class="opacity-70"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
		<span><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
	</div>
<?php endforeach; ?>

<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
	<div class="text-sm py-2 border-t border-black/10">
		<?php wc_cart_totals_shipping_html(); ?>
	</div>
<?php endif; ?>

<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
	<div class="flex items-center justify-between text-sm py-2 border-t border-black/10">
		<span class="opacity-70"><?php echo esc_html( $fee->name ); ?></span>
		<span><?php wc_cart_totals_fee_html( $fee ); ?></span>
	</div>
<?php endforeach; ?>

<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
	<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
		<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
			<div class="flex items-center justify-between text-sm py-2 border-t border-black/10">
				<span class="opacity-70"><?php echo esc_html( $tax->label ); ?></span>
				<span><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<div class="flex items-center justify-between text-sm py-2 border-t border-black/10">
			<span class="opacity-70"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
			<span><?php wc_cart_totals_taxes_total_html(); ?></span>
		</div>
	<?php endif; ?>
<?php endif; ?>

<div class="flex items-center justify-between text-sm py-2 border-t border-black/10">
	<span class="opacity-70"><?php esc_html_e( 'مبلغ قابل پرداخت:', 'negarin' ); ?></span>
	<span><?php wc_cart_totals_order_total_html(); ?></span>
</div>
