<?php
/**
 * Enqueue Tailwind/Alpine build output, with Vite HMR support during development.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the Vite dev server is running (a .vite-dev flag file is written by `npm run dev`).
 */
function negarin_is_vite_dev(): bool {
	return defined( 'WP_DEBUG' ) && WP_DEBUG && file_exists( NEGARIN_DIR . '/.vite-dev' );
}

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'dashicons' );

		if ( negarin_is_vite_dev() ) {
			wp_enqueue_script( 'negarin-vite-client', 'http://localhost:5173/@vite/client', array(), null, false );
			wp_enqueue_script( 'negarin-app', 'http://localhost:5173/assets/js/app.js', array(), null, true );
			add_filter(
				'script_loader_tag',
				function ( $tag, $handle ) {
					if ( in_array( $handle, array( 'negarin-vite-client', 'negarin-app' ), true ) ) {
						$tag = str_replace( ' src', ' type="module" src', $tag );
					}
					return $tag;
				},
				10,
				2
			);
			return;
		}

		$manifest_path = NEGARIN_DIR . '/assets/build/.vite/manifest.json';

		if ( ! file_exists( $manifest_path ) ) {
			return;
		}

		$manifest = json_decode( (string) file_get_contents( $manifest_path ), true );
		$entry    = $manifest['assets/js/app.js'] ?? null;

		if ( ! $entry ) {
			return;
		}

		if ( ! empty( $entry['css'][0] ) ) {
			wp_enqueue_style( 'negarin-app', NEGARIN_URI . '/assets/build/' . $entry['css'][0], array(), NEGARIN_VERSION );
		}
		wp_enqueue_script( 'negarin-app', NEGARIN_URI . '/assets/build/' . $entry['file'], array(), NEGARIN_VERSION, true );
		wp_script_add_data( 'negarin-app', 'type', 'module' );

		wp_localize_script(
			'negarin-app',
			'negarinData',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'restUrl'   => esc_url_raw( rest_url( 'negarin/v1/' ) ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'isRtl'     => is_rtl(),
				'cartCount' => function_exists( 'WC' ) ? WC()->cart->get_cart_contents_count() : 0,
			)
		);
	}
);

add_action(
	'admin_enqueue_scripts',
	function () {
		wp_enqueue_style( 'negarin-admin', NEGARIN_URI . '/assets/css/admin.css', array(), NEGARIN_VERSION );
	}
);
