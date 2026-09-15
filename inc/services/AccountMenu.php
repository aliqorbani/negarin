<?php
/**
 * Reshapes the WooCommerce "My Account" menu to the three items the design
 * shows (اطلاعات من / سفارش‌های من / خروج) — still built from WooCommerce's
 * own endpoint system, so any endpoint added by another plugin is simply
 * absent unless explicitly re-added here, rather than hand-rolled.
 *
 * @package Negarin
 */

namespace Negarin\Services;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AccountMenu {

    public function __construct() {
        add_filter( 'woocommerce_account_menu_items', array( $this, 'trim_menu' ) );
    }

    public function trim_menu( array $items ): array {
        $keep = array_intersect_key(
            $items,
            array_flip( array( 'edit-account', 'orders', 'customer-logout' ) )
        );

        // Re-label to match the export exactly.
        if ( isset( $keep['edit-account'] ) ) {
            $keep['edit-account'] = __( 'اطلاعات من', 'negarin' );
        }
        if ( isset( $keep['orders'] ) ) {
            $keep['orders'] = __( 'سفارش‌های من', 'negarin' );
        }
        if ( isset( $keep['customer-logout'] ) ) {
            $keep['customer-logout'] = __( 'خروج از حساب کاربری', 'negarin' );
        }

        // Preserve a sane order regardless of what core/plugins registered.
        $order = array( 'edit-account', 'orders', 'customer-logout' );
        uksort( $keep, fn( $a, $b ) => array_search( $a, $order, true ) <=> array_search( $b, $order, true ) );

        return $keep;
    }

    public static function order_count( int $user_id ): int {
        return (int) wc_get_customer_order_count( $user_id );
    }

    public static function svgicon_for( string $endpoint ): string {
        return match ( $endpoint ) {
            'edit-account'     => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 21C20 19.6044 20 18.9067 19.8278 18.3389C19.44 17.0605 18.4395 16.06 17.1611 15.6722C16.5933 15.5 15.8956 15.5 14.5 15.5H9.5C8.10444 15.5 7.40665 15.5 6.83886 15.6722C5.56045 16.06 4.56004 17.0605 4.17224 18.3389C4 18.9067 4 19.6044 4 21M16.5 7.5C16.5 9.98528 14.4853 12 12 12C9.51472 12 7.5 9.98528 7.5 7.5C7.5 5.01472 9.51472 3 12 3C14.4853 3 16.5 5.01472 16.5 7.5Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'orders'            => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.0004 9V6C16.0004 3.79086 14.2095 2 12.0004 2C9.79123 2 8.00037 3.79086 8.00037 6V9M3.59237 10.352L2.99237 16.752C2.82178 18.5717 2.73648 19.4815 3.03842 20.1843C3.30367 20.8016 3.76849 21.3121 4.35839 21.6338C5.0299 22 5.94374 22 7.77142 22H16.2293C18.057 22 18.9708 22 19.6423 21.6338C20.2322 21.3121 20.6971 20.8016 20.9623 20.1843C21.2643 19.4815 21.179 18.5717 21.0084 16.752L20.4084 10.352C20.2643 8.81535 20.1923 8.04704 19.8467 7.46616C19.5424 6.95458 19.0927 6.54511 18.555 6.28984C17.9444 6 17.1727 6 15.6293 6L8.37142 6C6.82806 6 6.05638 6 5.44579 6.28984C4.90803 6.54511 4.45838 6.95458 4.15403 7.46616C3.80846 8.04704 3.73643 8.81534 3.59237 10.352Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'customer-logout'   => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 7L21 12L16 17M21 12H9M9 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V16.2C3 17.8802 3 18.7202 3.32698 19.362C3.6146 19.9265 4.07354 20.3854 4.63803 20.673C5.27976 21 6.11984 21 7.8 21H9" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            default             => '',
        };
    }

    /**
     * Label of the account endpoint currently being viewed, or null on the
     * dashboard root (no endpoint). Used by template-parts/header/site-header.php
     * to render the mobile "back to menu" row only where it's needed.
     */
    public static function current_endpoint_label(): ?string {
        if ( ! is_wc_endpoint_url() ) {
            return null;
        }

        foreach ( wc_get_account_menu_items() as $endpoint => $label ) {
            if ( is_wc_endpoint_url( $endpoint ) ) {
                return $label;
            }
        }

        return null;
    }
}