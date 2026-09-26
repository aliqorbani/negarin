<?php
/**
 * "به سبد خرید اضافه شد" — included once in footer.php, same as
 * toast-container.php. Opens on the `negarin:cart-added-modal` window
 * event, which assets/js/cart-added-modal.js dispatches after every
 * *successful* add-to-cart (native shop-loop/single-product AJAX button,
 * or the size-select REST endpoint via assets/js/size-select.js). Per the
 * 2026-09 decision this replaces the toast that used to confirm the same
 * thing — see toast.js for the notice-parsing flow this supersedes.
 *
 * Same bottom-sheet-on-mobile / centered-dialog-on-desktop pattern as
 * size-select-modal.php. Self-contained `x-data` — unlike that modal,
 * nothing else on the page needs to read or set this state.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div
	x-data="{ open: false }"
	@negarin:cart-added-modal.window="open = true"
	x-show="open"
	x-cloak
	class="fixed inset-0 z-[60] flex items-end md:items-center justify-center"
>
	<div class="absolute inset-0 bg-black/50" @click="open = false"></div>

	<div
		class="relative bg-white w-full md:max-w-md md:mx-4 rounded-t-2xl md:rounded-sm p-6 md:p-8 text-right"
		@click.outside="open = false"
		x-transition:enter="duration-200 ease-out"
		x-transition:enter-start="opacity-0 translate-y-4 md:translate-y-0 md:scale-95"
		x-transition:enter-end="opacity-100 translate-y-0 md:scale-100"
		x-transition:leave="duration-150 ease-in"
		x-transition:leave-start="opacity-100"
		x-transition:leave-end="opacity-0"
	>
		<div class="flex items-center justify-between pb-4 border-b border-negarin-line mb-6">
			<h3 class="font-serif text-base md:text-xl order-1 flex items-center gap-2">
				<span class="shrink-0 flex items-center justify-center size-7 rounded-full bg-emerald-100 text-emerald-600">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</span>
				<?php esc_html_e( 'به سبد خرید اضافه شد', 'negarin' ); ?>
			</h3>
			<button type="button" @click="open = false" aria-label="<?php esc_attr_e( 'بستن', 'negarin' ); ?>" class="text-2xl leading-none order-2">&times;</button>
		</div>

		<div class="flex flex-col md:flex-row gap-3">
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn--outline flex-1 text-center">
				<?php esc_html_e( 'ادامه خرید', 'negarin' ); ?>
			</a>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="btn btn--solid flex-1 text-center">
				<?php esc_html_e( 'تکمیل خرید', 'negarin' ); ?>
			</a>
		</div>
	</div>
</div>
