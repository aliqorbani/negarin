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

<form class="woocommerce-EditAccountForm edit-account p-16" action="" method="post">
    <?php do_action( 'woocommerce_edit_account_form_start' ); ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

        <div class="form-row">
            <label for="account_first_name"><?php esc_html_e( 'نام', 'negarin' ); ?></label>
            <input type="text" class="input-text" name="account_first_name" id="account_first_name" value="<?php echo esc_attr( $user->first_name ?: WC()->customer->get_billing_first_name() ); ?>">
        </div>

        <div class="form-row">
            <label for="account_last_name"><?php esc_html_e( 'نام خانوادگی', 'negarin' ); ?></label>
            <input type="text" class="input-text" name="account_last_name" id="account_last_name" value="<?php echo esc_attr( $user->last_name ?? WC()->customer->get_billing_last_name() ); ?>">
        </div>

        <div class="form-row">
            <label for="negarin_referral_source"><?php esc_html_e( 'نحوه آشنایی با نگارین :)', 'negarin' ); ?></label>
            <input type="text" class="input-text" name="negarin_referral_source" id="negarin_referral_source" value="<?php echo esc_attr( AddressBook::get_referral_source( $user->ID ) ); ?>">
        </div>

        <div class="form-row">
            <label for="account_email"><?php esc_html_e( 'آدرس ایمیل', 'negarin' ); ?></label>
            <input type="email" class="input-text" name="account_email" id="account_email" value="<?php echo esc_attr( $user->user_email ?? WC()->customer->get_billing_email() ); ?>">
        </div>

        <div class="md:col-span-2 bg-white border border-[#e4e7ec] p-5 flex items-start justify-between">
            <div class="text-right">
                <p class="text-lg font-medium mb-1"><?php esc_html_e( 'منزل', 'negarin' ); ?></p>
                <p class="text-sm">
                    <?php echo $address ? wp_kses_post( $address ) : esc_html__( 'آدرسی ثبت نشده است.', 'negarin' ); ?>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?php echo esc_url( AddressBook::clear_address_url() ); ?>" aria-label="<?php esc_attr_e( 'حذف آدرس', 'negarin' ); ?>" onclick="return confirm('<?php esc_attr_e( 'آدرس منزل حذف شود؟', 'negarin' ); ?>');">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 3H15M3 6H21M19 6L18.2987 16.5193C18.1935 18.0975 18.1409 18.8867 17.8 19.485C17.4999 20.0118 17.0472 20.4353 16.5017 20.6997C15.882 21 15.0911 21 13.5093 21H10.4907C8.90891 21 8.11803 21 7.49834 20.6997C6.95276 20.4353 6.50009 20.0118 6.19998 19.485C5.85911 18.8867 5.8065 18.0975 5.70129 16.5193L5 6M10 10.5V15.5M14 10.5V15.5" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', 'billing', wc_get_page_permalink( 'myaccount' ) ) ); ?>" aria-label="<?php esc_attr_e( 'ویرایش آدرس', 'negarin' ); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 20H21M3 20H4.67454C5.16372 20 5.40832 20 5.63849 19.9447C5.84256 19.8957 6.03765 19.8149 6.2166 19.7053C6.41843 19.5816 6.59138 19.4086 6.93729 19.0627L19.5 6.49998C20.3285 5.67156 20.3285 4.32841 19.5 3.49998C18.6716 2.67156 17.3285 2.67156 16.5 3.49998L3.93726 16.0627C3.59136 16.4086 3.4184 16.5816 3.29472 16.7834C3.18506 16.9624 3.10425 17.1574 3.05526 17.3615C3 17.5917 3 17.8363 3 18.3255V20Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>

    </div>

    <?php do_action( 'woocommerce_edit_account_form' ); ?>

    <button type="submit" class="btn btn--solid w-full mt-8"><?php esc_html_e( 'ذخیره تغییرات', 'negarin' ); ?></button>
    <?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
    <input type="hidden" name="action" value="save_account_details">

    <?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>