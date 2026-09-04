<?php
/**
 * Included once, in footer.php. Listens for the `negarin:toast` window
 * event (assets/js/toast.js) and renders a stacked, auto-dismissing list.
 * The event carries { message, type, duration } — type is one of
 * success | error | info, duration in ms (defaults to 10s upstream).
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div
	x-data
	@negarin:toast.window="$store.toast.push($event.detail.message, $event.detail.type, $event.detail.duration)"
	class="fixed inset-x-4 top-4 md:inset-x-auto md:top-6 md:left-6 z-[100] flex flex-col gap-2 pointer-events-none"
>
	<template x-for="item in $store.toast.items" :key="item.id">
		<div
			class="pointer-events-auto flex items-start gap-3 rounded-sm px-4 py-3 text-sm shadow-lg text-white md:max-w-sm transition-all"
			:class="{
				'bg-emerald-600': item.type === 'success',
				'bg-negarin-red': item.type === 'error',
				'bg-negarin-ink': item.type === 'info',
			}"
			x-transition:enter="duration-200 ease-out"
			x-transition:enter-start="opacity-0 -translate-y-2"
			x-transition:enter-end="opacity-100 translate-y-0"
			x-transition:leave="duration-150 ease-in"
			x-transition:leave-start="opacity-100"
			x-transition:leave-end="opacity-0"
		>
			<span class="flex-1" x-text="item.message"></span>
			<button type="button" class="opacity-70 hover:opacity-100 leading-none" @click="$store.toast.remove(item.id)" aria-label="<?php esc_attr_e( 'بستن', 'negarin' ); ?>">&times;</button>
		</div>
	</template>
</div>
