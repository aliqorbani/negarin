<?php
/**
 * Live "quick search" REST endpoint that powers the header search modal
 * (see template-parts/header/search-modal.php + assets/js/search.js).
 * Public and read-only — no nonce required, same as any normal search.
 *
 * GET /wp-json/negarin/v1/search?q=TERM
 *   -> { results: [...up to MAX_RESULTS published products], total, search_url }
 *
 * @package Negarin
 */

namespace Negarin\Services;

use WP_REST_Request;
use WP_REST_Response;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QuickSearch {

	private const MAX_RESULTS = 8;
	private const MIN_CHARS   = 2;

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			'negarin/v1',
			'/search',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_search' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'q' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	public function handle_search( WP_REST_Request $request ): WP_REST_Response {
		$term = trim( (string) $request->get_param( 'q' ) );

		if ( mb_strlen( $term ) < self::MIN_CHARS ) {
			return new WP_REST_Response(
				array(
					'results' => array(),
					'total'   => 0,
				),
				200
			);
		}

		$query = new WP_Query(
			array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				's'                   => $term,
				'posts_per_page'      => self::MAX_RESULTS,
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);

		$results = array();

		foreach ( $query->posts as $post ) {
			$product = wc_get_product( $post );

			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}

			$image_url = wp_get_attachment_image_url( $product->get_image_id(), 'negarin-product-card' );

			$results[] = array(
				'id'        => $product->get_id(),
				'title'     => $product->get_name(),
				'permalink' => get_permalink( $product->get_id() ),
				// Already-escaped HTML from WooCommerce (may include <del>/<ins> for sale prices).
				'price'     => wp_kses_post( $product->get_price_html() ),
				'image'     => $image_url ? $image_url : wc_placeholder_img_src( 'thumbnail' ),
				'in_stock'  => $product->is_in_stock(),
			);
		}

		wp_reset_postdata();

		return new WP_REST_Response(
			array(
				'results'    => $results,
				'total'      => count( $results ),
				'search_url' => add_query_arg(
					array(
						's'         => rawurlencode( $term ),
						'post_type' => 'product',
					),
					home_url( '/' )
				),
			),
			200
		);
	}
}
