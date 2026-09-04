<?php
/**
 * Included once, in footer.php. Listens for the `negarin:toast` window
 * event (assets/js/toast.js) and renders a stacked, auto-dismissing list.
 * The event carries { message, type, duration } — type is one of
 * success | error | info, duration in ms (defaults to 10s upstream).
 *
 * Styled like SweetAlert2's toast mode: white card, colored icon, message —
 * rather than a small solid-color pill.
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
        class="fixed inset-x-4 top-4 md:inset-x-auto md:top-6 md:left-6 z-[100] flex flex-col gap-3 pointer-events-none"
>
    <template x-for="item in $store.toast.items" :key="item.id">
        <div
                class="pointer-events-auto flex items-center gap-3 rounded-lg bg-white px-5 py-4 shadow-xl ring-1 ring-black/5 md:min-w-[360px] md:max-w-md transition-all"
                x-transition:enter="duration-200 ease-out"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="duration-150 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
        >
			<span
                    class="shrink-0 flex items-center justify-center w-9 h-9 rounded-full"
                    :class="{
					'bg-emerald-100 text-emerald-600': item.type === 'success',
					'bg-red-100 text-negarin-red': item.type === 'error',
					'bg-amber-100 text-negarin-gold': item.type === 'info',
				}"
            >
				<svg x-show="item.type === 'success'" width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
				<svg x-show="item.type === 'error'" width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
				<svg x-show="item.type === 'info'" width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 11v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="8" r="1" fill="currentColor"/></svg>
			</span>

            <span class="flex-1 text-base leading-snug text-negarin-ink" x-text="item.message"></span>

            <button type="button" class="shrink-0 text-black/40 hover:text-black/70 leading-none text-xl" @click="$store.toast.remove(item.id)" aria-label="<?php esc_attr_e( 'بستن', 'negarin' ); ?>">&times;</button>
        </div>
    </template>
</div>