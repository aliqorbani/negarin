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

	public static function dashicon_for( string $endpoint ): string {
		return match ( $endpoint ) {
			'edit-account'     => 'dashicons-admin-users',
			'orders'            => 'dashicons-bag',
			'customer-logout'   => 'dashicons-migrate',
			default             => 'dashicons-marker',
		};
	}
}
