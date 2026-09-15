<?php
/**
 * WooCommerce checkout override.
 *
 * IMPORTANT: this is still a single <form name="checkout"> — exactly what
 * WooCommerce's core checkout.js expects (AJAX order review, payment
 * gateway toggling, validation, `#place_order` submit). We only wrap two
 * halves of it in Alpine `x-show` panels so it *looks* like two screens;
 * nothing about WooCommerce's own checkout processing is reimplemented.
 * This keeps every payment gateway plugin compatible with zero extra work.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_ajax() ) {
    do_action( 'woocommerce_before_checkout_form', $checkout );

    if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
        echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'برای تکمیل خرید باید وارد حساب کاربری خود شوید.', 'negarin' ) ) );
        return;
    }
}
?>

    <div class="container max-w-7xl mx-auto px-4 pt-8">
    <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" x-data="negarinCheckoutForm">

        <?php if ( $checkout->get_checkout_fields() ) : ?>
            <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

            <div class="container max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-[1fr_320px] gap-8 items-start">

                <div class="order-1">

                    <!-- Step 1: address -->
                    <div x-show="step === 1" x-cloak id="customer_details">
                        <?php do_action( 'woocommerce_checkout_billing' ); ?>
                    </div>

                    <!-- Step 2: payment -->
                    <div x-show="step === 2" x-cloak id="order_review" class="woocommerce-checkout-review-order">
                        <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                    </div>

                </div>

                <div class="order-2 md:sticky md:top-24">
                    <div class="bg-white p-4 text-right border border-negarin-line">
                        <h2 class="font-serif text-lg mb-4 mt-0.5"><?php esc_html_e( 'فاکتور شما', 'negarin' ); ?></h2>

                        <div id="negarin-order-totals">
                            <?php get_template_part( 'template-parts/components/order-totals-rows' ); ?>
                        </div>

                        <div class="text-sm bg-white rounded-sm px-4 py-3 my-4 flex items-center gap-2">
                            <span>🎁</span>
                            <span><?php esc_html_e( 'ارسال رو مهمان نگارین هستید :)', 'negarin' ); ?></span>
                        </div>

                        <?php wc_get_template( 'checkout/form-coupon.php' ); ?>

                        <button type="button" x-show="step === 1" class="btn btn--solid w-full" @click="goToPayment()">
                            <?php esc_html_e( 'تایید و ادامه', 'negarin' ); ?>
                        </button>
                        <!-- Step 2's real submit button is WooCommerce's own #place_order,
                             rendered inside woocommerce_checkout_payment via checkout/payment.php.
                             assets/js/checkout.js physically moves it here (Figma has the
                             button in the sidebar for both steps) and re-runs that move after
                             every `updated_checkout` AJAX refresh, since WooCommerce replaces
                             #order_review's whole markup on every totals/gateway change. -->
                        <div id="negarin-place-order-slot" x-show="step === 2" x-cloak></div>
                    </div>

                    <?php
                    $negarin_terms_page = negarin_option( 'checkout_terms_page' );
                    $negarin_terms_url  = $negarin_terms_page ? get_permalink( $negarin_terms_page ) : '';
                    ?>
                    <?php if ( $negarin_terms_url ) : ?>
                        <a href="<?php echo esc_url( $negarin_terms_url ); ?>" class="border border-negarin-line flex items-center justify-center gap-2 px-4 py-3 mt-4 text-sm">
                            <span><?php esc_html_e( 'شرایطی که قبل از ثبت سفارش باید بخوانید', 'negarin' ); ?></span>
                            <span>💌</span>
                        </a>
                    <?php endif; ?>
                </div>

            </div>

            <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
        <?php endif; ?>

    </form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>