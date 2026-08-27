<?php
/**
 * Product card for the shop/archive/category grid. Shows the featured
 * image by default and cross-fades to the product's first gallery image
 * on hover — the standard "two photos per product" pattern requested,
 * reusing WooCommerce's own gallery data (no extra field needed).
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product || ! $product->is_visible() ) {
	return;
}

$gallery_ids = $product->get_gallery_image_ids();
$hover_id    = $gallery_ids[0] ?? null;
$main_id     = $product->get_image_id();
?>
<div <?php wc_product_class( 'product-card group', $product ); ?>>

	<a href="<?php echo esc_url( get_permalink() ); ?>" class="block relative overflow-hidden bg-negarin-cream aspect-[3/5.4]">

		<?php if ( $product->is_on_sale() ) : ?>
			<span class="absolute top-3 <?php echo is_rtl() ? 'right-3' : 'left-3'; ?> z-10 bg-negarin-ink text-white text-[10px] px-2 py-1">
				<?php esc_html_e( 'تخفیف', 'negarin' ); ?>
			</span>
		<?php endif; ?>

		<?php if ( $main_id ) : ?>
			<?php negarin_image( (int) $main_id, 'negarin-section-half', 'absolute inset-0 w-full h-full object-cover transition-opacity duration-500' . ( $hover_id ? ' group-hover:opacity-0' : '' ) ); ?>
		<?php else : ?>
			<?php echo wc_placeholder_img( 'negarin-section-half', array( 'class' => 'absolute inset-0 w-full h-full object-cover' ) ); // phpcs:ignore ?>
		<?php endif; ?>

		<?php if ( $hover_id ) : ?>
			<?php negarin_image( (int) $hover_id, 'negarin-section-half', 'absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100' ); ?>
		<?php endif; ?>

		<?php if ( ! $product->is_in_stock() ) : ?>
			<span class="absolute inset-x-0 bottom-0 bg-white/90 text-center text-xs py-2">
				<?php esc_html_e( 'ناموجود', 'negarin' ); ?>
			</span>
		<?php endif; ?>
	</a>

	<div class="product-card__body pt-3 text-right">
		<a href="<?php echo esc_url( get_permalink() ); ?>" class="product-card__title block truncate"><?php the_title(); ?></a>
		<span class="product-card__price block"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
	</div>

</div>
