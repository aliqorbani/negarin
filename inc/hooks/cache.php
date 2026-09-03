<?php
/**
 * LiteSpeed Cache integration.
 *
 * A belt-and-braces guard alongside the LiteSpeed Cache dashboard config
 * (Cache -> Excludes -> Do Not Cache URIs). Uses LiteSpeed's own developer
 * API to mark session-sensitive pages as never-cache in code, so the
 * exclusion survives even if the dashboard setting is ever reset, changed
 * by a plugin update, or simply forgotten on a future staging/prod clone.
 *
 * Root cause this addresses: My Account (and cart/checkout) render
 * differently for a logged-in visitor than for a guest. If LiteSpeed ever
 * serves a cached "guest" snapshot of one of these URLs to a logged-in
 * customer, the page looks logged out even though the auth cookie is
 * still perfectly valid.
 *
 * Reuses the same "which pages carry session state" definition Turbo
 * already relies on (negarin_is_turbo_excluded_page(), see
 * inc/hooks/turbo.php) so the two stay in sync automatically.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action(
    'wp',
    function () {
        if ( ! defined( 'LSCWP_V' ) ) {
            return; // LiteSpeed Cache plugin not active.
        }

        if ( function_exists( 'negarin_is_turbo_excluded_page' ) && negarin_is_turbo_excluded_page() ) {
            do_action( 'litespeed_control_set_nocache', 'negarin: session-sensitive WooCommerce page' );
        }
    }
);

/**
 * Never cache the OTP REST endpoints. These responses (send code / verify
 * code + set auth cookie) are per-visitor and must never be replayed from
 * cache to a different visitor. LiteSpeed does not page-cache POST requests
 * by default, so this is a defensive second layer rather than the fix for
 * the reported bug.
 */
add_action(
    'rest_api_init',
    function () {
        if ( ! defined( 'LSCWP_V' ) ) {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        if ( str_contains( $uri, '/wp-json/negarin/v1/otp' ) ) {
            do_action( 'litespeed_control_set_nocache', 'negarin: otp endpoint' );
        }
    },
    5
);
