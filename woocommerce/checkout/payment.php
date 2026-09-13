<?php
/**
 * Checkout payment section override. Keeps every functional element
 * WooCommerce's checkout.js and payment gateway plugins depend on exactly
 * as-is (the #payment wrapper, .wc_payment_methods list, #place_order
 * button with its id/name, the checkout nonce) — only restyled.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! wp_doing_ajax() ) {
    do_action( 'woocommerce_review_order_before_payment' );
}
?>
    <div id="payment" class="woocommerce-checkout-payment mt-6 bg-white">
        <?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
            <ul class="wc_payment_methods payment_methods methods" aria-label="<?php esc_attr_e( 'Payment methods', 'woocommerce' ); ?>">
                <?php
                if ( ! empty( $available_gateways ) ) {
                    foreach ( $available_gateways as $gateway ) {
                        wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
                    }
                } else {
                    echo '<li>';
                    wc_print_notice(
                            apply_filters(
                                    'woocommerce_no_available_payment_methods_message',
                                    WC()->customer->get_billing_country()
                                            ? esc_html__( 'متأسفانه در حال حاضر روش پرداختی در دسترس نیست. برای راهنمایی با ما تماس بگیرید.', 'negarin' )
                                            : esc_html__( 'لطفاً مشخصات بالا را تکمیل کنید تا روش‌های پرداخت نمایش داده شود.', 'negarin' )
                            ),
                            'notice'
                    );
                    echo '</li>';
                }
                ?>
            </ul>
        <?php endif; ?>

        <div class="form-row place-order mt-4">
            <noscript>
                <?php
                printf(
                /* translators: $1 and $2 opening and closing emphasis tags respectively */
                        esc_html__( 'چون جاوااسکریپت در مرورگر شما غیرفعال است، لطفاً قبل از ثبت سفارش روی %1$sبروزرسانی مجموع%2$s بزنید.', 'negarin' ),
                        '<em>',
                        '</em>'
                );
                ?>
                <br/><button type="submit" class="btn btn--outline mt-2" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'بروزرسانی مجموع', 'negarin' ); ?>"><?php esc_html_e( 'بروزرسانی مجموع', 'negarin' ); ?></button>
            </noscript>

            <!-- No checkout/terms.php here on purpose: Figma has neither a terms
                 checkbox nor a privacy-policy paragraph in this spot — the
                 "شرایطی که..." badge/link in the sidebar covers it instead.
                 woocommerce_checkout_show_terms is also filtered to false in
                 CheckoutFields.php so the required-terms validation doesn't
                 block orders now that the checkbox never renders. -->

            <?php do_action( 'woocommerce_review_order_before_submit' ); ?>

            <?php
            echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    'woocommerce_order_button_html',
                    '<button type="submit" class="btn btn--solid w-full mt-4" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>'
            );
            ?>

            <?php do_action( 'woocommerce_review_order_after_submit' ); ?>

            <?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
        </div>
    </div>
<?php
if ( ! wp_doing_ajax() ) {
    do_action( 'woocommerce_review_order_after_payment' );
}