<?php
/**
 * Two small additions to the account profile screen:
 *  - a real "delete address" action for the trash icon next to "منزل"
 *    (WooCommerce itself has no concept of deleting an address, only
 *    editing it, so this clears the billing_* fields back to empty).
 *  - a custom "نحوه آشنایی با نگارین" profile field, stored as user meta.
 *
 * @package Negarin
 */

namespace Negarin\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AddressBook {

	private const REFERRAL_META_KEY = 'negarin_referral_source';

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'maybe_clear_address' ) );
		add_action( 'woocommerce_save_account_details', array( $this, 'save_referral_source' ) );
	}

	public static function get_referral_source( int $user_id ): string {
		return (string) get_user_meta( $user_id, self::REFERRAL_META_KEY, true );
	}

	public function save_referral_source( int $user_id ): void {
		if ( isset( $_POST['negarin_referral_source'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WC's own account-details nonce already guards this action.
			update_user_meta( $user_id, self::REFERRAL_META_KEY, sanitize_text_field( wp_unslash( $_POST['negarin_referral_source'] ) ) );
		}
	}

	public static function clear_address_url(): string {
		return wp_nonce_url(
			add_query_arg( 'negarin_clear_address', '1', wc_get_account_endpoint_url( 'edit-account' ) ),
			'negarin_clear_address'
		);
	}

	public function maybe_clear_address(): void {
		if ( empty( $_GET['negarin_clear_address'] ) || ! is_user_logged_in() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		check_admin_referer( 'negarin_clear_address' );

		$user_id = get_current_user_id();

		foreach ( array( 'address_1', 'address_2', 'city', 'state', 'postcode', 'plaque', 'unit' ) as $field ) {
			delete_user_meta( $user_id, 'billing_' . $field );
		}

		wp_safe_redirect( wc_get_account_endpoint_url( 'edit-account' ) );
		exit;
	}
}
