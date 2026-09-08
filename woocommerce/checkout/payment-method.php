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
<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?> border border-negarin-line rounded-sm mb-2 overflow-hidden">
    <label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>" class="flex items-center gap-3 px-4 py-3 cursor-pointer">
        <input
                id="payment_method_<?php echo esc_attr( $gateway->id ); ?>"
                type="radio"
                class="input-radio"
                name="payment_method"
                value="<?php echo esc_attr( $gateway->id ); ?>"
                <?php checked( $gateway->chosen, true ); ?>
                data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>"
        />
        <span class="flex-1"><?php echo $gateway->get_title(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?></span>
        <?php echo $gateway->get_icon(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
    </label>

    <?php if ( $gateway->get_description() ) : ?>
        <p class="px-4 pb-3 text-xs text-[#98a2b3]"><?php echo wp_kses_post( $gateway->get_description() ); ?></p>
    <?php endif; ?>

    <?php if ( $gateway->has_fields() ) : ?>
        <div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?> px-4 pb-4 text-sm opacity-80" <?php echo $gateway->chosen ? '' : 'style="display:none;"'; ?>>
            <?php $gateway->payment_fields(); ?>
        </div>
    <?php endif; ?>
</li>