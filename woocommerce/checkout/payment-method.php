<?php
/**
 * A single payment gateway row. Keeps the exact IDs/names/classes
 * WooCommerce's checkout.js needs (`payment_method_{id}` input,
 * `payment_box payment_method_{id}` fields wrapper, `chosen` state) —
 * only the surrounding markup/styling changes.
 *
 * @package Negarin
 * @var WC_Payment_Gateway $gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
    <label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>" class="flex items-center justify-start gap-2 px-4 md:px-6 py-3 md:py-4 cursor-pointer">
        <span class="relative inline-flex size-5 shrink-0">
            <input
                    id="payment_method_<?php echo esc_attr( $gateway->id ); ?>"
                    type="radio"
                    class="peer sr-only"
                    name="payment_method"
                    value="<?php echo esc_attr( $gateway->id ); ?>"
                    <?php checked( $gateway->chosen, true ); ?>
                    data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>"
            />
            <span class="absolute inset-0 rounded-full border border-black"></span>
            <span class="absolute inset-0 m-auto size-[10px] scale-0 rounded-full bg-[#333] transition-transform peer-checked:scale-100"></span>
        </span>
        <span class="flex items-center gap-2">
            <?php echo $gateway->get_icon(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
            <span class="text-base font-medium text-black"><?php echo $gateway->get_title(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?></span>
        </span>
    </label>

    <?php if ( $gateway->get_description() ) : ?>
        <p class="-mt-1 md:pr-14 md:px-6 px-4 text-[#98a2b3] text-sm"><?php echo wp_kses_post( $gateway->get_description() ); ?></p>
    <?php endif; ?>

    <?php if ( $gateway->has_fields() ) : ?>
        <div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?> px-4 md:px-6 pb-4 text-sm opacity-80" <?php echo $gateway->chosen ? '' : 'style="display:none;"'; ?>>
            <?php $gateway->payment_fields(); ?>
        </div>
    <?php endif; ?>
</li>