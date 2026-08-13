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
//remove_action( 'woocommerce_login_form', 'woocommerce_login_form' );
//add_action( 'woocommerce_login_form', function () {
//	get_template_part( 'template-parts/components/otp-login-form' );
//} );

// Disable user registration via wp-login.php?action=register for the storefront.
add_filter(
	'option_users_can_register',
	function ( $value ) {
		return is_admin() ? $value : false;
	}
);
