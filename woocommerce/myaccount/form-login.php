<?php
/**
 * WooCommerce My Account login form override.
 *
 * Customers authenticate via phone + OTP.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_before_customer_login_form' );

get_template_part( 'template-parts/components/otp-login-form' );

do_action( 'woocommerce_after_customer_login_form' );