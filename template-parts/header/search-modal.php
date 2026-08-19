<?php
/**
 * Full-page search modal: dark backdrop behind a centered "spotlight"
 * panel — search input on top, live AJAX results directly beneath it as
 * the shopper types. Opened via `searchOpen` (declared in
 * site-header.php), the same boolean the old inline show/hide search
 * form used to key off of.
 *
 * The <form> still has a real method="get" action, so pressing Enter (or
 * the "مشاهده همه نتایج" link) lands on a normal WooCommerce product
 * search-results page — the AJAX panel is progressive enhancement on top
 * of that, not a replacement for it.
 *
 * Talks to the REST route in inc/services/QuickSearch.php through the
 * Alpine component in assets/js/search.js.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div
	x-data="negarinSearch()"
	x-show="searchOpen"
	x-cloak
	class="fixed inset-0 z-[70]"
	@keydown.escape.window="searchOpen = false"
>
	<!-- Backdrop -->
	<div
		x-show="searchOpen"
		x-transition:enter="transition-opacity ease-out duration-300"
		x-transition:enter-start="opacity-0"
		x-transition:enter-end="opacity-100"
		x-transition:leave="transition-opacity linear duration-200"
		x-transition:leave-start="opacity-100"
		x-transition:leave-end="opacity-0"
		class="absolute inset-0 bg-black/75"
		@click="searchOpen = false"
	></div>

	<!-- Panel wrapper: near the top on mobile (so the keyboard doesn't cover it), vertically centered from md up -->
	<div class="relative h-full overflow-y-auto flex items-start md:items-center justify-center px-4 pt-20 pb-10 md:pt-10">
		<div
			x-show="searchOpen"
			x-transition:enter="transition ease-out duration-300"
			x-transition:enter-start="opacity-0 -translate-y-3"
			x-transition:enter-end="opacity-100 translate-y-0"
			x-transition:leave="transition ease-in duration-150"
			x-transition:leave-start="opacity-100 translate-y-0"
			x-transition:leave-end="opacity-0 -translate-y-3"
			class="relative w-full max-w-xl bg-white rounded-sm shadow-xl <?php echo is_admin_bar_showing() ? 'md:translate-y-4' : ''; ?>"
			@click.outside="searchOpen = false"
		>
			<button
				type="button"
				class="absolute -top-10 left-0 text-white/90 hover:text-white text-2xl leading-none"
				@click="searchOpen = false"
				aria-label="<?php esc_attr_e( 'بستن جستجو', 'negarin' ); ?>"
			>
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
					<path d="M14.1667 5.83331L5.83337 14.1666M5.83337 5.83331L14.1667 14.1666" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>

			<form
				role="search"
				method="get"
				action="<?php echo esc_url( home_url( '/' ) ); ?>"
				class="flex items-center gap-3 px-5 py-4 border-b border-negarin-line"
			>
				<input type="hidden" name="post_type" value="product">

				<svg width="19" height="19" viewBox="0 0 19 19" fill="none" class="shrink-0 opacity-50">
					<path d="M18.5 18.5L14.15 14.15M16.5 8.5C16.5 12.9183 12.9183 16.5 8.5 16.5C4.08172 16.5 0.5 12.9183 0.5 8.5C0.5 4.08172 4.08172 0.5 8.5 0.5C12.9183 0.5 16.5 4.08172 16.5 8.5Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>

				<input
					type="search"
					name="s"
					x-model="query"
					x-ref="input"
					placeholder="<?php esc_attr_e( 'جستجوی محصولات…', 'negarin' ); ?>"
					autocomplete="off"
					class="flex-1 border-0 focus:ring-0 text-base p-0 bg-transparent placeholder:opacity-50"
				>

				<span
					x-show="loading"
					x-cloak
					class="w-4 h-4 shrink-0 border-2 border-negarin-ink/25 border-t-negarin-ink rounded-full animate-spin"
				></span>
			</form>

			<div class="max-h-[60vh] overflow-y-auto">

				<template x-if="query.trim().length > 0 && query.trim().length < 2">
					<p class="px-5 py-6 text-sm text-center opacity-50"><?php esc_html_e( 'حداقل ۲ حرف وارد کنید', 'negarin' ); ?></p>
				</template>

				<template x-if="error">
					<p class="px-5 py-6 text-sm text-center text-negarin-red" x-text="error"></p>
				</template>

				<template x-if="!error && searched && !loading && results.length === 0 && query.trim().length >= 2">
					<p class="px-5 py-6 text-sm text-center opacity-50"><?php esc_html_e( 'محصولی یافت نشد', 'negarin' ); ?></p>
				</template>

				<ul x-show="results.length > 0" class="divide-y divide-negarin-line">
					<template x-for="item in results" :key="item.id">
						<li>
							<a :href="item.permalink" class="flex items-center gap-4 px-5 py-3 hover:bg-negarin-cream/60 transition-colors" @click="searchOpen = false">
								<span class="shrink-0 w-14 h-16 bg-negarin-cream overflow-hidden">
									<img :src="item.image" :alt="item.title" class="w-full h-full object-cover">
								</span>
								<span class="flex-1 min-w-0 text-right">
									<span class="block truncate text-sm" x-text="item.title"></span>
									<span class="block text-sm opacity-70 mt-1" x-html="item.price"></span>
								</span>
							</a>
						</li>
					</template>
				</ul>

			</div>

			<div x-show="query.trim().length >= 2 && results.length > 0" x-cloak class="border-t border-negarin-line px-5 py-3 text-center">
				<a
					:href="`<?php echo esc_url( home_url( '/' ) ); ?>?s=${encodeURIComponent(query.trim())}&post_type=product`"
					class="text-sm underline underline-offset-4"
				>
					<?php esc_html_e( 'مشاهده همه نتایج', 'negarin' ); ?>
				</a>
			</div>

		</div>
	</div>
</div>
