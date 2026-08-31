<?php
/**
 * WooCommerce's default dashboard root content ("Hello X, from your
 * dashboard you can...") is intentionally not shown — the account-menu
 * partial (see my-account.php + template-parts/components/account-menu.php)
 * already covers greeting + navigation, so this text would just be
 * redundant. Kept as an empty override, rather than left un-overridden,
 * so a WooCommerce core update can't silently bring the text back.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_account_dashboard' );