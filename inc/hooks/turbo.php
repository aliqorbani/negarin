<?php
/**
 * Turbo Drive integration glue.
 *
 * Turbo Drive itself is wired up on the JS side (assets/js/spa.js). This
 * file only handles the two things WordPress/PHP has to cooperate on:
 *
 * 1. Telling Turbo to do a full, normal browser navigation — never a
 *    soft/AJAX one — for cart, checkout, and My Account. Those pages carry
 *    server session state (WooCommerce session, payment nonces, gateway
 *    redirects) that a partial DOM swap has no business touching.
 * 2. Marking our own script/style tags with `data-turbo-track="reload"` so
 *    that if a deploy ships a new asset hash, Turbo notices and does a
 *    full reload instead of soft-navigating with stale JS/CSS still
 *    loaded in the tab.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request is one of the state-heavy WooCommerce flows
 * that should always be a full page load, never a Turbo visit.
 */
function negarin_is_turbo_excluded_page(): bool {
	if ( ! function_exists( 'is_cart' ) ) {
		return false;
	}

	return is_cart() || is_checkout() || is_account_page();
}

add_action(
	'wp_head',
	function () {
		if ( negarin_is_turbo_excluded_page() ) {
			echo '<meta name="turbo-visit-control" content="reload">' . "\n";
		}
	},
	1
);

add_filter(
	'script_loader_tag',
	function ( $tag, $handle ) {
		if ( 'negarin-app' === $handle || 'negarin-vite-client' === $handle ) {
			$tag = str_replace( ' src', ' data-turbo-track="reload" src', $tag );
		}
		return $tag;
	},
	10,
	2
);

add_filter(
	'style_loader_tag',
	function ( $tag, $handle ) {
		if ( 'negarin-app' === $handle ) {
			$tag = str_replace( ' href', ' data-turbo-track="reload" href', $tag );
		}
		return $tag;
	},
	10,
	2
);
