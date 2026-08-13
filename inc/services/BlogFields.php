<?php
/**
 * Per-post SEO + "related products" fields for the blog.
 *
 * SEO note: if an SEO plugin (Yoast/RankMath) is active, these meta/OG
 * fields are deliberately NOT duplicated in <head> — see inc/services/Seo.php.
 * The focus-keyword + manual-relation fields here are still useful on
 * their own for driving the "related products" block regardless of which
 * SEO plugin (if any) is active.
 *
 * @package Negarin
 */

namespace Negarin\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BlogFields {

	public function __construct() {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'    => 'group_negarin_post_seo',
				'title'  => __( 'SEO & Related Products', 'negarin' ),
				'fields' => array(
					array(
						'key'          => 'field_focus_keyword',
						'name'         => 'focus_keyword',
						'label'        => __( 'کلیدواژه کانونی', 'negarin' ),
						'instructions' => __( 'در صورت خالی بودن فیلد "محصولات مرتبط دستی"، این کلیدواژه برای پیدا کردن محصولات مرتبط به‌صورت خودکار استفاده می‌شود.', 'negarin' ),
						'type'         => 'text',
					),
					array(
						'key'          => 'field_meta_description',
						'name'         => 'meta_description',
						'label'        => __( 'توضیحات متا (SEO)', 'negarin' ),
						'instructions' => __( 'اگر افزونه Yoast یا RankMath فعال باشد، این فیلد نادیده گرفته می‌شود (از تنظیمات همان افزونه استفاده کنید).', 'negarin' ),
						'type'         => 'textarea',
						'rows'         => 2,
						'maxlength'    => 160,
					),
					array(
						'key'          => 'field_related_products',
						'name'         => 'related_products',
						'label'        => __( 'محصولات مرتبط (دستی)', 'negarin' ),
						'instructions' => __( 'خالی بگذارید تا بر اساس کلیدواژه کانونی به‌صورت خودکار پیدا شوند.', 'negarin' ),
						'type'         => 'relationship',
						'post_type'    => array( 'product' ),
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'post',
						),
					),
				),
			)
		);
	}

	/**
	 * Resolve related products for a post: manual selection first,
	 * falling back to a keyword search against the focus keyword.
	 *
	 * @return \WC_Product[]
	 */
	public static function get_related_products( int $post_id, int $limit = 4 ): array {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array();
		}

		$manual = get_field( 'related_products', $post_id );

		if ( ! empty( $manual ) ) {
			return array_filter( array_map( 'wc_get_product', wp_list_pluck( $manual, 'ID' ) ) );
		}

		$keyword = get_field( 'focus_keyword', $post_id );

		if ( ! $keyword ) {
			return array();
		}

		$query = new \WP_Query(
			array(
				'post_type'      => 'product',
				'posts_per_page' => $limit,
				's'              => $keyword,
			)
		);

		return array_filter( array_map( 'wc_get_product', wp_list_pluck( $query->posts, 'ID' ) ) );
	}
}
