<?php
/**
 * WooCommerce "اطلاعات من" (edit-account) override.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Negarin\Services\AddressBook;

$user    = wp_get_current_user();

// `wc_format_address()` doesn't exist in WooCommerce; the correct API for
// turning an address array into formatted HTML is WC_Countries::get_formatted_address().
$address = WC()->countries->get_formatted_address(
        array(
                'address_1' => get_user_meta( $user->ID, 'billing_address_1', true ),
                'address_2' => get_user_meta( $user->ID, 'billing_address_2', true ),
                'city'      => get_user_meta( $user->ID, 'billing_city', true ),
                'state'     => get_user_meta( $user->ID, 'billing_state', true ),
                'postcode'  => get_user_meta( $user->ID, 'billing_postcode', true ),
                'country'   => get_user_meta( $user->ID, 'billing_country', true ) ?: 'IR',
        )
);

do_action( 'woocommerce_before_edit_account_form' ); ?>

<form class="woocommerce-EditAccountForm edit-account" action="" method="post">
    <?php do_action( 'woocommerce_edit_account_form_start' ); ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

        <div class="form-row">
            <label for="account_last_name"><?php esc_html_e( 'نام خانوادگی', 'negarin' ); ?></label>
            <input type="text" class="input-text" name="account_last_name" id="account_last_name" value="<?php echo esc_attr( $user->last_name ); ?>">
        </div>

        <div class="form-row">
            <label for="account_first_name"><?php esc_html_e( 'نام', 'negarin' ); ?></label>
            <input type="text" class="input-text" name="account_first_name" id="account_first_name" value="<?php echo esc_attr( $user->first_name ); ?>">
        </div>

        <div class="form-row">
            <label for="negarin_referral_source"><?php esc_html_e( 'نحوه آشنایی با نگارین :)', 'negarin' ); ?></label>
            <input type="text" class="input-text" name="negarin_referral_source" id="negarin_referral_source" value="<?php echo esc_attr( AddressBook::get_referral_source( $user->ID ) ); ?>">
        </div>

        <div class="form-row">
            <label for="account_email"><?php esc_html_e( 'آدرس ایمیل', 'negarin' ); ?></label>
            <input type="email" class="input-text" name="account_email" id="account_email" value="<?php echo esc_attr( $user->user_email ); ?>">
        </div>

        <div class="md:col-span-2 bg-negarin-cream p-5 flex items-start justify-between">
            <div class="flex items-center gap-3">
                <a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', 'billing', wc_get_page_permalink( 'myaccount' ) ) ); ?>" aria-label="<?php esc_attr_e( 'ویرایش آدرس', 'negarin' ); ?>">
                    <span class="dashicons dashicons-edit"></span>
                </a>
                <a href="<?php echo esc_url( AddressBook::clear_address_url() ); ?>" aria-label="<?php esc_attr_e( 'حذف آدرس', 'negarin' ); ?>" onclick="return confirm('<?php esc_attr_e( 'آدرس منزل حذف شود؟', 'negarin' ); ?>');">
                    <span class="dashicons dashicons-trash"></span>
                </a>
            </div>
            <div class="text-right">
                <p class="text-sm mb-1"><?php esc_html_e( 'منزل', 'negarin' ); ?></p>
                <p class="text-sm opacity-70">
                    <?php echo $address ? wp_kses_post( $address ) : esc_html__( 'آدرسی ثبت نشده است.', 'negarin' ); ?>
                </p>
            </div>
        </div>

    </div>

    <?php do_action( 'woocommerce_edit_account_form' ); ?>

    <button type="submit" class="btn btn--solid w-full mt-8"><?php esc_html_e( 'ذخیره تغییرات', 'negarin' ); ?></button>
    <?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
    <input type="hidden" name="action" value="save_account_details">

    <?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>
