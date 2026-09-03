<?php
/**
 * Customers authenticate exclusively via phone + OTP (see Services/OtpAuth.php).
 * These guards close off the traditional wp-login.php email/username/password
 * path for non-admin roles, and point WooCommerce's "my account" login form
 * at our custom OTP UI instead.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Disable classic password authentication for the `customer` role.
add_filter(
    'wp_authenticate_user',
    function ( $user, $password ) {
        if ( is_wp_error( $user ) ) {
            return $user;
        }
        if ( in_array( 'customer', (array) $user->roles, true ) ) {
            return new WP_Error( 'negarin_otp_only', __( 'ورود با رمز عبور غیرفعال است. لطفاً از ورود با شماره موبایل استفاده کنید.', 'negarin' ) );
        }
        return $user;
    },
    20,
    2
);

// Remove WooCommerce's default username/password login form on My Account
// and replace it with the phone/OTP template part.
remove_action( 'woocommerce_login_form', 'woocommerce_login_form' );
add_action( 'woocommerce_login_form', function () {
    get_template_part( 'template-parts/components/otp-login-form' );
} );

// Disable user registration via wp-login.php?action=register for the storefront.
add_filter(
    'option_users_can_register',
    function ( $value ) {
        return is_admin() ? $value : false;
    }
);

/**
 * Warn in wp-admin when OTP codes are only being logged (no Kavenegar API
 * key configured, or NEGARIN_OTP_TEST_MODE forced on) — so nobody mistakes
 * test mode for a working SMS setup, or ships to production this way by
 * accident. See Services/OtpAuth::gateway() and Services/Sms/LogGateway.php.
 */
add_action(
    'admin_notices',
    function () {
        $otp_sms_configured = defined( 'NEGARIN_MELLIPAYAMAK_API_KEY' ) && ! empty( NEGARIN_MELLIPAYAMAK_API_KEY );
        $force_test_mode      = defined( 'NEGARIN_OTP_TEST_MODE' ) && NEGARIN_OTP_TEST_MODE;

        if ( ( $force_test_mode || ! $otp_sms_configured ) && current_user_can( 'activate_plugins' ) ) {
            echo '<div class="notice notice-warning"><p>' .
                esc_html__( 'Negarin: OTP codes are currently only being logged (WooCommerce → Status → Logs → source "negarin-otp"), not sent as real SMS. Define NEGARIN_KAVENEGAR_API_KEY in wp-config.php before launch.', 'negarin' ) .
                '</p></div>';
        }
    }
);
