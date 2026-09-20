<?php
/**
 * "انتخاب سایز" — template-parts/components/size-select-button.php includes
 * this right after its trigger button. One other modal stacks on top of
 * this one at a higher z-index (closing it reveals this modal again,
 * unchanged, underneath — same pattern used everywhere else in the theme
 * for nested modals): size-chart-modal.php via `sizeChartOpen` ("راهنمای
 * سایز" link).
 *
 * Per the 2026-09 decision there's no "سفارش شخصی" (custom order)
 * fallback anymore — customers pick only from the sizes this product has
 * a variation for.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Negarin\Services\ProductSizing;

global $product;

if ( ! $product instanceof WC_Product_Variable ) {
    return;
}

$size_options = ProductSizing::get_size_options( $product );

$component_state = wp_json_encode(
        array(
                'productId' => $product->get_id(),
                'options'   => $size_options,
        )
);
?>
<div x-show="sizeSelectOpen" x-cloak class="fixed inset-0 z-50 flex items-end md:items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click="sizeSelectOpen = false"></div>

    <div
            x-data="negarinSizeSelect(<?php echo esc_attr( $component_state ); ?>)"
            class="relative bg-white w-full md:max-w-2xl max-h-[90vh] overflow-y-auto rounded-t-2xl md:rounded-sm p-6 md:p-8 text-right"
            @click.outside="if (!sizeChartOpen) sizeSelectOpen = false"
    >

        <div class="flex items-center justify-between pb-4 border-b border-negarin-line mb-6">
            <h3 class="font-serif text-base md:text-xl order-1"><?php esc_html_e( 'انتخاب سایز', 'negarin' ); ?></h3>
            <button @click="sizeSelectOpen = false" aria-label="<?php esc_attr_e( 'بستن', 'negarin' ); ?>" class="text-2xl leading-none order-2">&times;</button>
        </div>

        <div class="flex flex-wrap gap-2 mb-3">
            <template x-for="option in options" :key="option.slug">
                <button
                        type="button"
                        class="size-8 flex items-center justify-center border text-sm relative overflow-hidden"
                        :class="{
						'border-negarin-line text-negarin-ink': option.in_stock && selected !== option.slug,
						'border-negarin-ink bg-negarin-ink text-white': selected === option.slug,
						'border-negarin-line text-black/30 cursor-not-allowed': !option.in_stock,
					}"
                        :disabled="!option.in_stock"
                        @click="selectSize(option)"
                        x-text="option.label"
                ></button>
            </template>
        </div>

        <div class="flex justify-end mb-6">
            <button type="button" class="text-sm underline" @click="sizeChartOpen = true">
                <?php esc_html_e( 'راهنمای سایز', 'negarin' ); ?>
            </button>
        </div>

        <p class="text-negarin-red text-sm mb-4" x-show="error" x-text="error"></p>

        <div class="flex gap-3">
            <button type="button" class="btn btn--solid flex-1" :disabled="!selected || loading" @click="addToCart()">
                <span x-show="!loading"><?php esc_html_e( 'ثبت و ادامه سفارش', 'negarin' ); ?></span>
                <span x-show="loading"><?php esc_html_e( 'در حال ثبت...', 'negarin' ); ?></span>
            </button>
        </div>
    </div>

    <?php get_template_part( 'template-parts/components/size-chart-modal' ); ?>
</div>