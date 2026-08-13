<?php
/**
 * Small, reusable template helpers. Kept as plain functions (not classes)
 * since they're called dozens of times per page render.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Echo a responsive <img> for an ACF image field (stored as attachment ID).
 *
 * @param int|null $image_id ACF image field value (return_format = id).
 * @param string   $size     Registered image size.
 * @param string   $classes  Extra CSS classes.
 * @param bool     $lazy     Whether to lazy-load (first hero image should pass false).
 */
function negarin_image( ?int $image_id, string $size = 'large', string $classes = '', bool $lazy = true ): void {
	if ( ! $image_id ) {
		return;
	}

	$attrs = array(
		'class'   => $classes,
		'loading' => $lazy ? 'lazy' : 'eager',
		'decoding' => 'async',
	);

	echo wp_get_attachment_image( $image_id, $size, false, $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Render an ACF "link" field array as an <a> tag, or nothing if empty.
 *
 * @param array|null $link  ACF link field value: ['url'=>, 'title'=>, 'target'=>].
 * @param string     $classes CSS classes for the anchor.
 */
function negarin_link_button( ?array $link, string $classes = 'btn' ): void {
	if ( empty( $link['url'] ) ) {
		return;
	}

	printf(
		'<a href="%1$s" class="%2$s"%3$s>%4$s</a>',
		esc_url( $link['url'] ),
		esc_attr( $classes ),
		! empty( $link['target'] ) ? ' target="_blank" rel="noopener"' : '',
		esc_html( $link['title'] ?? __( 'مشاهده', 'negarin' ) )
	);
}

/**
 * Fetch a Theme Option value with a fallback, shortening `get_field(..., 'option')`.
 *
 * @param string $key     Field name registered in ThemeOptions.
 * @param mixed  $fallback Fallback value.
 * @return mixed
 */
function negarin_option( string $key, $fallback = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $fallback;
	}
	$value = get_field( $key, 'option' );
	return ( null === $value || '' === $value ) ? $fallback : $value;
}

/**
 * Simple breadcrumb trail: [['label'=>, 'url'=>], ...]. Used for both the
 * visible breadcrumb UI and the BreadcrumbList JSON-LD in Seo.php, so the
 * two always stay in sync.
 */
function negarin_get_breadcrumbs(): array {
	$crumbs = array(
		array( 'label' => __( 'خانه', 'negarin' ), 'url' => home_url( '/' ) ),
	);

	if ( is_singular( 'post' ) ) {
		$blog_page_id = get_option( 'page_for_posts' );
		if ( $blog_page_id ) {
			$crumbs[] = array( 'label' => get_the_title( $blog_page_id ), 'url' => get_permalink( $blog_page_id ) );
		}

		$categories = get_the_category();
		if ( ! empty( $categories ) ) {
			$crumbs[] = array( 'label' => $categories[0]->name, 'url' => get_category_link( $categories[0] ) );
		}

		$crumbs[] = array( 'label' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_category() || is_tag() || is_date() ) {
		$crumbs[] = array( 'label' => single_cat_title( '', false ) ?: get_the_archive_title(), 'url' => '' );
	} elseif ( is_singular( 'product' ) ) {
		$crumbs[] = array( 'label' => __( 'محصولات', 'negarin' ), 'url' => get_post_type_archive_link( 'product' ) );
		$crumbs[] = array( 'label' => get_the_title(), 'url' => get_permalink() );
	}

	return $crumbs;
}

/**
 * Estimated reading time in minutes (~150 wpm). Approximate for Persian
 * text since word-boundary counting isn't as precise as for Latin script,
 * but close enough for a UI label like "۴ دقیقه مطالعه".
 */
function negarin_reading_time( string $content ): int {
	$word_count = count( preg_split( '/\s+/u', trim( wp_strip_all_tags( $content ) ) ) );
	return max( 1, (int) ceil( $word_count / 150 ) );
}

/**
 * Injects id="" anchors into every H2/H3 in post content and returns a
 * flat list of [ 'id', 'text', 'level' ] for rendering the TOC box.
 * Content and TOC are built from the exact same pass, so they can never
 * drift out of sync with each other.
 *
 * @return array{content: string, items: array}
 */
function negarin_extract_toc( string $content ): array {
	$items = array();

	$content = preg_replace_callback(
		'/<h([23])(.*?)>(.*?)<\/h\1>/i',
		function ( $match ) use ( &$items ) {
			$level = (int) $match[1];
			$text  = wp_strip_all_tags( $match[3] );
			$slug  = sanitize_title( $text ) . '-' . ( count( $items ) + 1 );

			$items[] = array(
				'id'    => $slug,
				'text'  => $text,
				'level' => $level,
			);

			return sprintf( '<h%1$d id="%2$s"%3$s>%4$s</h%1$d>', $level, esc_attr( $slug ), $match[2], $match[3] );
		},
		$content
	);

	return array(
		'content' => $content,
		'items'   => $items,
	);
}

/**
 * Section background color -> Tailwind class map, kept in one place so the
 * design system stays consistent (design tokens live in tailwind.config.js).
 */
function negarin_section_bg_class( string $key ): string {
	$map = array(
		'white' => 'bg-white text-negarin-ink',
		'cream' => 'bg-negarin-cream text-negarin-ink',
		'black' => 'bg-negarin-ink text-white',
	);

	return $map[ $key ] ?? $map['white'];
}
