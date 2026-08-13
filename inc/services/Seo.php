<?php
/**
 * Lightweight, non-duplicating SEO output:
 *  - JSON-LD structured data (Article + BreadcrumbList) always outputs —
 *    schema markup doesn't conflict with Yoast/RankMath's own schema in
 *    any harmful way, but we still check for it to avoid doubling up.
 *  - <meta name="description">, canonical, and Open Graph tags only output
 *    when neither Yoast nor RankMath is active, since those plugins own
 *    that job (and do it more completely) once installed.
 *
 * @package Negarin
 */

namespace Negarin\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Seo {

	public function __construct() {
		add_action( 'wp_head', array( $this, 'meta_description_and_og' ), 1 );
		add_action( 'wp_head', array( $this, 'json_ld' ), 5 );
	}

	private function has_seo_plugin(): bool {
		return defined( 'WPSEO_VERSION' ) || class_exists( '\RankMath' );
	}

	public function meta_description_and_og(): void {
		if ( $this->has_seo_plugin() || ! is_singular( array( 'post', 'product' ) ) ) {
			return;
		}

		$description = $this->get_description();

		if ( ! $description ) {
			return;
		}

		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
		printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'product' ) ? 'product' : 'article' );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( get_the_title() ) );
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( get_permalink() ) );

		$image_id = is_singular( 'product' ) ? wc_get_product()->get_image_id() : get_post_thumbnail_id();
		if ( $image_id ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( wp_get_attachment_image_url( $image_id, 'negarin-hero' ) ) );
		}
	}

	private function get_description(): string {
		$field = get_field( 'meta_description' );

		if ( $field ) {
			return $field;
		}

		$source = is_singular( 'product' ) ? wc_get_product()->get_short_description() : get_the_excerpt();

		return wp_trim_words( wp_strip_all_tags( $source ), 30, '…' );
	}

	public function json_ld(): void {
		if ( is_singular( 'post' ) ) {
			$this->article_schema();
		}

		$this->breadcrumb_schema();
	}

	private function article_schema(): void {
		global $post;

		$schema = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'headline'         => get_the_title(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author(),
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id'   => get_permalink(),
			),
		);

		$thumbnail_id = get_post_thumbnail_id( $post );
		if ( $thumbnail_id ) {
			$schema['image'] = wp_get_attachment_image_url( $thumbnail_id, 'negarin-hero' );
		}

		$description = $this->get_description();
		if ( $description ) {
			$schema['description'] = $description;
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n"; // phpcs:ignore
	}

	private function breadcrumb_schema(): void {
		$crumbs = negarin_get_breadcrumbs();

		if ( count( $crumbs ) < 2 ) {
			return;
		}

		$items = array();
		foreach ( $crumbs as $index => $crumb ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => $crumb['label'],
				'item'     => $crumb['url'],
			);
		}

		$schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n"; // phpcs:ignore
	}
}
