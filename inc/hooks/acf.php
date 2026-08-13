<?php
/**
 * Sync ACF field group definitions to /acf-json so they're version-controlled
 * alongside the theme instead of living only in the database.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'acf/settings/save_json',
	function () {
		return NEGARIN_DIR . '/acf-json';
	}
);

add_filter(
	'acf/settings/load_json',
	function ( $paths ) {
		unset( $paths[0] );
		$paths[] = NEGARIN_DIR . '/acf-json';
		return $paths;
	}
);

/**
 * Friendly admin notice if ACF isn't active — this theme depends on it.
 */
add_action(
	'admin_notices',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) && current_user_can( 'activate_plugins' ) ) {
			echo '<div class="notice notice-error"><p>' .
				esc_html__( 'Negarin theme requires the free "Advanced Custom Fields" plugin to be installed and active.', 'negarin' ) .
				'</p></div>';
		}
		if ( ! class_exists( 'WooCommerce' ) && current_user_can( 'activate_plugins' ) ) {
			echo '<div class="notice notice-error"><p>' .
				esc_html__( 'Negarin theme requires WooCommerce to be installed and active.', 'negarin' ) .
				'</p></div>';
		}
	}
);
