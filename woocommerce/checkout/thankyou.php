<?php
/**
 * WooCommerce order-received (thank you) page override.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $order ) {
	return;
}
?>

<div class="max-w-2xl mx-auto text-center py-16 px-6">

	<?php if ( $order->has_status( 'failed' ) ) : ?>

		<h1 class="font-serif text-2xl mb-4"><?php esc_html_e( 'پرداخت ناموفق بود', 'negarin' ); ?></h1>
		<p class="opacity-70 mb-8"><?php esc_html_e( 'متأسفانه پرداخت شما تکمیل نشد. لطفاً دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.', 'negarin' ); ?></p>
		<div class="flex items-center justify-center gap-3">
			<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="btn btn--solid"><?php esc_html_e( 'تلاش مجدد برای پرداخت', 'negarin' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--outline"><?php esc_html_e( 'بازگشت به صفحه اصلی', 'negarin' ); ?></a>
		</div>

	<?php else : ?>

		<div class="w-16 h-16 rounded-full border-2 border-emerald-500 text-emerald-500 flex items-center justify-center mx-auto mb-6 text-3xl">
			✓
		</div>

		<h1 class="font-serif text-2xl mb-8"><?php esc_html_e( 'نوش تن و جانِ شما :)', 'negarin' ); ?></h1>

		<div class="flex items-center justify-center gap-3 mb-10">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--outline"><?php esc_html_e( 'بازگشت به صفحه اصلی', 'negarin' ); ?></a>
			<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="btn btn--solid"><?php esc_html_e( 'پیگیری سفارش', 'negarin' ); ?></a>
		</div>

		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php endif; ?>

</div>
