<?php
/**
 * Product gallery — responsive behavior differs by breakpoint to match
 * the two separate exports:
 *  - Mobile: a swipeable horizontal carousel, one image at a time, with
 *    dot indicators.
 *  - Desktop (md+): every image stacks full-width, one after another.
 * Both are driven by the same markup — only the CSS layout (flex row
 * with scroll-snap vs. block stacking) changes per breakpoint, so there's
 * one source of truth for the image list.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product ) {
	return;
}

$attachment_ids = $product->get_gallery_image_ids();
$main_image_id  = $product->get_image_id();

if ( $main_image_id ) {
	array_unshift( $attachment_ids, $main_image_id );
}
?>
<div
	class="negarin-product-gallery"
	x-data="{
		lightbox: null,
		active: 0,
		onScroll(el) {
			this.active = Math.round(el.scrollLeft / el.clientWidth);
		}
	}"
>

	<?php if ( empty( $attachment_ids ) ) : ?>
		<div class="w-full"><?php echo wc_placeholder_img( 'negarin-hero' ); // phpcs:ignore ?></div>
	<?php endif; ?>

	<div
		class="flex md:block overflow-x-auto md:overflow-visible snap-x snap-mandatory md:snap-none scrollbar-none"
		@scroll.debounce.100ms="onScroll($el)"
	>
		<?php foreach ( $attachment_ids as $index => $attachment_id ) : ?>
			<button
				type="button"
				class="block w-full shrink-0 snap-center md:shrink md:snap-align-none"
				@click="lightbox = <?php echo (int) $index; ?>"
				aria-label="<?php esc_attr_e( 'بزرگ‌نمایی تصویر', 'negarin' ); ?>"
			>
				<?php negarin_image( (int) $attachment_id, 'negarin-hero', 'w-full h-auto object-cover', 0 !== $index ); ?>
			</button>
		<?php endforeach; ?>
	</div>

	<?php if ( count( $attachment_ids ) > 1 ) : ?>
		<div class="flex md:hidden items-center justify-center gap-1.5 py-3">
			<?php foreach ( $attachment_ids as $index => $attachment_id ) : ?>
				<span
					class="w-1.5 h-1.5 rounded-full transition-colors"
					:class="active === <?php echo (int) $index; ?> ? 'bg-negarin-ink' : 'bg-black/20'"
				></span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div x-show="lightbox !== null" x-cloak class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4" @click="lightbox = null">
		<?php foreach ( $attachment_ids as $index => $attachment_id ) : ?>
			<img x-show="lightbox === <?php echo (int) $index; ?>" src="<?php echo esc_url( wp_get_attachment_image_url( (int) $attachment_id, 'large' ) ); ?>" class="max-h-full max-w-full object-contain" alt="">
		<?php endforeach; ?>
	</div>
</div>
