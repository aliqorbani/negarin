<?php
/**
 * Section: Product Carousel — pulls from a category or a manual product list.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$title  = get_sub_field( 'pc_title' );
$source = get_sub_field( 'source' ) ?: 'category';

if ( 'manual' === $source ) {
	$product_ids = wp_list_pluck( (array) get_sub_field( 'products' ), 'ID' );
	$query_args  = array(
		'post_type'      => 'product',
		'post__in'       => $product_ids ?: array( 0 ),
		'orderby'        => 'post__in',
		'posts_per_page' => 12,
	);
} else {
	$category   = get_sub_field( 'category' );
	$query_args = array(
		'post_type'      => 'product',
		'posts_per_page' => 12,
		'tax_query'      => $category ? array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $category,
			),
		) : array(),
	);
}

$products = new WP_Query( $query_args );

if ( ! $products->have_posts() ) {
	return;
}
?>
<section class="negarin-product-carousel py-12">
	<div class="max-w-7xl mx-auto px-4">
		<?php if ( $title ) : ?>
			<h3 class="font-serif text-2xl mb-6 text-right"><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="flex gap-4 overflow-x-auto scrollbar-none snap-x snap-mandatory pb-2" x-data>
			<?php
			while ( $products->have_posts() ) :
				$products->the_post();
				global $product;
				?>
				<div class="snap-start shrink-0 w-[70%] sm:w-[45%] md:w-[23%]">
					<?php wc_get_template_part( 'content', 'product' ); ?>
				</div>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
