<?php
/**
 * WooCommerce template override: content-single-product.php
 * Replaces the default hook-driven layout with the theme's two-column
 * (stacked gallery + narrow summary) design.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! is_singular( 'product' ) ) {
	return;
}

?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?> x-data="{ customOrderOpen: false, sizeGuideOpen: false, specsOpen: false, careOpen: false }">

	<?php do_action( 'woocommerce_before_single_product' ); ?>

	<div class="container max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 md:grid-cols-[1fr_380px] gap-8">

		<div class="md:order-1">
			<?php get_template_part( 'template-parts/components/product-gallery' ); ?>
		</div>

		<div class="md:order-2 md:sticky md:top-24 md:self-start text-right py-4 flex flex-col">

			<div class="order-2 md:order-1">
				<h1 class="font-serif text-2xl mb-4"><?php the_title(); ?></h1>

				<div class="flex items-center justify-between border-t border-black/10 py-4 text-sm">
					<span class="text-lg"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
					<span class="opacity-60"><?php esc_html_e( 'قیمت:', 'negarin' ); ?></span>
				</div>
			</div>

			<div class="order-1 md:order-2 grid grid-cols-2 gap-3 mt-2 md:mt-0">
				<?php if ( $product->is_in_stock() && $product->is_purchasable() ) : ?>
					<form method="post" action="<?php echo esc_url( $product->get_permalink() ); ?>">
						<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
						<input type="hidden" name="quantity" value="1">
						<button type="submit" class="btn btn--outline w-full ajax_add_to_cart add_to_cart_button">
							<?php esc_html_e( 'خرید کالای موجود', 'negarin' ); ?>
						</button>
					</form>
				<?php else : ?>
					<button type="button" class="btn btn--outline w-full opacity-40 cursor-not-allowed" disabled>
						<?php esc_html_e( 'ناموجود', 'negarin' ); ?>
					</button>
				<?php endif; ?>

				<?php get_template_part( 'template-parts/components/custom-order-button' ); ?>
			</div>

			<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
				<button type="button" class="order-3 w-full mt-3 border border-black/10 rounded-sm px-4 py-3 flex items-center justify-center gap-2 text-xs">
					<span><?php esc_html_e( 'این کالا را در ۴ قسط بخرید', 'negarin' ); ?></span>
					<span class="bg-[#1E88F0] text-white rounded px-2 py-1 font-bold text-[10px]">Snapp!Pay</span>
				</button>
			<?php endif; ?>

			<div class="order-4">
				<?php
				get_template_part(
					'template-parts/components/accordion-item',
					null,
					array(
						'title'   => __( 'مشخصات محصول', 'negarin' ),
						'content' => get_field( 'specifications' ),
					)
				);
				get_template_part(
					'template-parts/components/accordion-item',
					null,
					array(
						'title'   => __( 'مراقبت از محصول', 'negarin' ),
						'content' => get_field( 'care_instructions' ),
					)
				);
				?>
			</div>

		</div>
	</div>

	<?php do_action( 'woocommerce_after_single_product' ); ?>
</div>
