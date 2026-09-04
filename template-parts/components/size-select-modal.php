<?php
/**
 * "انتخاب سایز" — template-parts/components/size-select-button.php includes
 * this right after its trigger button. Two other modals stack on top of
 * this one at a higher z-index (closing either one reveals this modal
 * again, unchanged, underneath — same pattern used everywhere else in the
 * theme for nested modals):
 *   - size-chart-modal.php   via `sizeChartOpen`   ("راهنمای سایز" link)
 *   - custom-order-modal.php via `customOrderOpen` ("سفارش شخصی" button)
 *
 * Signed-out shoppers never see the custom-order modal open at all — the
 * trigger sends them to /my-account/ with a redirect_to back to this exact
 * product (?open_custom_order=1), which content-single-product.php reads
 * on load to re-open this flow automatically once they're logged in.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Negarin\Services\ProductSizing;
use Negarin\Services\CustomOrder;

global $product;

if ( ! $product instanceof WC_Product_Variable ) {
	return;
}

$size_options       = ProductSizing::get_size_options( $product );
$show_custom_order   = CustomOrder::is_available_for( $product );
$custom_order_url    = add_query_arg( 'open_custom_order', '1', get_permalink( $product->get_id() ) );
$login_redirect_url  = add_query_arg(
	'redirect_to',
	rawurlencode( $custom_order_url ),
	wc_get_page_permalink( 'myaccount' )
);

$component_state = wp_json_encode(
	array(
		'productId'       => $product->get_id(),
		'options'         => $size_options,
		'isLoggedIn'      => is_user_logged_in(),
		'loginRedirectUrl' => $login_redirect_url,
	)
);
?>
<div x-show="sizeSelectOpen" x-cloak class="fixed inset-0 z-50 flex items-end md:items-center justify-center">
	<div class="absolute inset-0 bg-black/50" @click="sizeSelectOpen = false"></div>

	<div
		x-data="negarinSizeSelect(<?php echo esc_attr( $component_state ); ?>)"
		class="relative bg-white w-full md:max-w-2xl max-h-[90vh] overflow-y-auto rounded-t-2xl md:rounded-sm p-6 md:p-8 text-right"
		@click.outside="sizeSelectOpen = false"
	>

		<div class="flex items-center justify-between pb-4 border-b border-negarin-line mb-6">
			<button @click="sizeSelectOpen = false" aria-label="<?php esc_attr_e( 'بستن', 'negarin' ); ?>" class="text-2xl leading-none order-1">&times;</button>
			<h3 class="font-serif text-base md:text-xl order-2"><?php esc_html_e( 'انتخاب سایز', 'negarin' ); ?></h3>
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

		<?php if ( $show_custom_order ) : ?>
			<button
				type="button"
				class="w-full flex items-center gap-2 bg-[#fffaeb] border border-[#dc6803] text-[#dc6803] rounded text-sm px-4 py-3 mb-6 text-right"
				@click="goToCustomOrder()"
			>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" class="shrink-0"><circle cx="12" cy="12" r="9" stroke="#DC6803" stroke-width="1.5"/><path d="M12 8v5" stroke="#DC6803" stroke-width="1.5" stroke-linecap="round"/><circle cx="12" cy="16" r="0.9" fill="#DC6803"/></svg>
				<span><?php esc_html_e( 'درصورت نبود سایز مدنظر، میتوانید از طریق سفارش شخصی اقدام کنید.', 'negarin' ); ?></span>
			</button>
		<?php endif; ?>

		<p class="text-negarin-red text-sm mb-4" x-show="error" x-text="error"></p>

		<div class="flex gap-3">
			<button type="button" class="btn btn--solid flex-1" :disabled="!selected || loading" @click="addToCart()">
				<span x-show="!loading"><?php esc_html_e( 'ثبت و ادامه سفارش', 'negarin' ); ?></span>
				<span x-show="loading"><?php esc_html_e( 'در حال ثبت...', 'negarin' ); ?></span>
			</button>
			<?php if ( $show_custom_order ) : ?>
				<button type="button" class="btn btn--outline flex-1" @click="goToCustomOrder()">
					<?php esc_html_e( 'سفارش شخصی', 'negarin' ); ?>
				</button>
			<?php endif; ?>
		</div>
	</div>

	<?php get_template_part( 'template-parts/components/size-chart-modal' ); ?>
	<?php if ( $show_custom_order ) : ?>
		<?php get_template_part( 'template-parts/components/custom-order-modal' ); ?>
	<?php endif; ?>
</div>
