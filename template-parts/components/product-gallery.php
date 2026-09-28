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
 * Clicking an image opens a lightbox (one full image at a time) that can be
 * navigated with the arrow buttons (desktop), the Left/Right keys, or by
 * dragging/swiping. All state and RTL handling live in
 * assets/js/product-gallery.js (`negarinProductGallery`).
 *
 * In this RTL theme the first image/dot sits on the right and "next" flows
 * to the left, so the lightbox arrows rely on flex order (first child =
 * right = previous) instead of hardcoded left/right positions.
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
$total          = count( $attachment_ids );

//if ( $main_image_id ) {
//	array_unshift( $attachment_ids, $main_image_id );
//}
?>
<div
        class="negarin-product-gallery"
        x-data="negarinProductGallery(<?php echo (int) $total; ?>)"
        @keydown.window="handleKeydown($event)"
>

    <?php if ( empty( $attachment_ids ) ) : ?>
        <div class="w-full"><?php echo wc_placeholder_img( 'negarin-hero' ); // phpcs:ignore ?></div>
    <?php endif; ?>

    <div
            x-ref="track"
            class="flex md:block overflow-x-auto md:overflow-visible snap-x snap-mandatory md:snap-none scrollbar-none"
    >
        <?php foreach ( $attachment_ids as $index => $attachment_id ) : ?>
            <button
                    type="button"
                    data-index="<?php echo (int) $index; ?>"
                    class="block w-full shrink-0 snap-center md:shrink md:snap-align-none"
                    @click="openLightbox(<?php echo (int) $index; ?>)"
                    aria-label="<?php esc_attr_e( 'بزرگ‌نمایی تصویر', 'negarin' ); ?>"
            >
                <?php negarin_image( (int) $attachment_id, 'negarin-product-card', 'image-size-negarin-product-card w-full h-auto object-cover', 0 !== $index ); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php if ( $total > 1 ) : ?>
        <div class="flex md:hidden items-center justify-center gap-1.5 py-3">
            <?php foreach ( $attachment_ids as $index => $attachment_id ) : ?>
                <span
                        class="w-1.5 h-1.5 rounded-full transition-colors"
                        :class="active === <?php echo (int) $index; ?> ? 'bg-negarin-ink' : 'bg-black/20'"
                ></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ( $total > 0 ) : ?>
        <div
                x-show="lightboxOpen"
                x-cloak
                class="fixed inset-0 z-50 bg-black/90"
                role="dialog"
                aria-modal="true"
                aria-label="<?php esc_attr_e( 'گالری تصاویر محصول', 'negarin' ); ?>"
        >
            <button
                    type="button"
                    class="absolute top-4 left-4 z-20 p-2 text-3xl leading-none text-white"
                    @click="closeLightbox()"
                    aria-label="<?php esc_attr_e( 'بستن', 'negarin' ); ?>"
            >&times;</button>

            <?php if ( $total > 1 ) : ?>
                <?php // First child sits on the right in RTL = previous; second = next (left). ?>
                <div class="pointer-events-none absolute inset-x-0 top-1/2 z-10 hidden -translate-y-1/2 items-center justify-between px-4 md:flex">
                    <button
                            type="button"
                            class="pointer-events-auto flex size-12 items-center justify-center rounded-full bg-white/15 text-white transition-colors hover:bg-white/30"
                            @click="prev()"
                            aria-label="<?php esc_attr_e( 'تصویر قبلی', 'negarin' ); ?>"
                    >
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <button
                            type="button"
                            class="pointer-events-auto flex size-12 items-center justify-center rounded-full bg-white/15 text-white transition-colors hover:bg-white/30"
                            @click="next()"
                            aria-label="<?php esc_attr_e( 'تصویر بعدی', 'negarin' ); ?>"
                    >
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
            <?php endif; ?>

            <div
                    class="flex h-full w-full touch-none select-none items-center justify-center"
                    :class="dragState ? 'cursor-grabbing' : 'cursor-grab'"
                    :style="{ transform: 'translateX(' + dragDelta + 'px)', transition: dragState ? 'none' : 'transform 200ms ease' }"
                    @click="onStageClick($event)"
                    @pointerdown="onDragStart($event)"
                    @pointermove.window="onDragMove($event)"
                    @pointerup.window="onDragEnd()"
                    @pointercancel.window="onDragEnd()"
            >
                <?php foreach ( $attachment_ids as $index => $attachment_id ) : ?>
                    <img
                            x-show="active === <?php echo (int) $index; ?>"
                            src="<?php echo esc_url( wp_get_attachment_image_url( (int) $attachment_id, 'full' ) ); ?>"
                            class="max-h-full max-w-full object-contain p-4"
                            draggable="false"
                            alt=""
                    >
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>