<?php
/**
 * WooCommerce shop/category archive override.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );
?>

<div class="container max-w-7xl mx-auto px-4 py-10 flex flex-col">

	<header class="text-center mb-10">
		<h1 class="font-serif text-3xl mb-2"><?php woocommerce_page_title(); ?></h1>
		<?php if ( is_product_category() ) : ?>
			<?php $term_description = term_description(); ?>
			<?php if ( $term_description ) : ?>
				<div class="opacity-70 text-sm max-w-2xl mx-auto"><?php echo wp_kses_post( $term_description ); ?></div>
			<?php endif; ?>
		<?php endif; ?>
	</header>

	<?php
	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
		)
	);
	?>
	<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
		<nav class="flex flex-wrap justify-center gap-3 mb-10" aria-label="<?php esc_attr_e( 'دسته‌بندی محصولات', 'negarin' ); ?>">
			<a
				href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>"
				class="px-4 py-2 text-sm rounded-full border <?php echo ( is_shop() ) ? 'bg-negarin-ink text-white border-negarin-ink' : 'border-black/15'; ?>"
			>
				<?php esc_html_e( 'همه محصولات', 'negarin' ); ?>
			</a>
			<?php foreach ( $categories as $category ) : ?>
				<a
					href="<?php echo esc_url( get_term_link( $category ) ); ?>"
					class="px-4 py-2 text-sm rounded-full border <?php echo ( is_product_category( $category->slug ) ) ? 'bg-negarin-ink text-white border-negarin-ink' : 'border-black/15'; ?>"
				>
					<?php echo esc_html( $category->name ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<?php if ( woocommerce_product_loop() ) : ?>

		<?php do_action( 'woocommerce_before_shop_loop' ); ?>

		<?php woocommerce_product_loop_start(); ?>

		<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php do_action( 'woocommerce_shop_loop' ); ?>
				<?php wc_get_template_part( 'content', 'product' ); ?>
			<?php endwhile; ?>
		<?php endif; ?>

		<?php woocommerce_product_loop_end(); ?>

		<?php do_action( 'woocommerce_after_shop_loop' ); ?>

	<?php else : ?>

		<?php do_action( 'woocommerce_no_products_found' ); ?>

	<?php endif; ?>

</div>

<?php
do_action( 'woocommerce_after_main_content' );
//do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
