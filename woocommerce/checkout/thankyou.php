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

		<div class="flex items-center justify-center mx-auto mb-6">
            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20.0007 32L28.0006 40L44.0006 24M58.6673 32C58.6673 46.7276 46.7282 58.6667 32.0006 58.6667C17.2731 58.6667 5.33398 46.7276 5.33398 32C5.33398 17.2724 17.2731 5.33337 32.0006 5.33337C46.7282 5.33337 58.6673 17.2724 58.6673 32Z" stroke="#17B26A" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

		<h1 class="font-serif text-2xl mb-8"><?php esc_html_e( 'نوش تن و جانِ شما ', 'negarin' ); ?> :‌)</h1>

		<div class="flex items-center justify-center gap-3 mb-10">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--outline"><?php esc_html_e( 'بازگشت به صفحه اصلی', 'negarin' ); ?></a>
			<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="btn btn--solid"><?php esc_html_e( 'پیگیری سفارش', 'negarin' ); ?></a>
		</div>

		<?php //do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php endif; ?>

</div>
