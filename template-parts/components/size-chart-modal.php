<?php
/**
 * "راهنمای سایز" — stacks on top of size-select-modal.php (z-60 over its
 * z-50) via `sizeChartOpen`. Closing it just flips that flag back off;
 * size-select-modal.php stays mounted and open underneath, unaffected —
 * same nested-modal pattern used by custom-order-modal.php.
 *
 * Content is a single ACF WYSIWYG field (Theme Options → Sizing Presets →
 * "محتوای راهنمای سایز") the admin edits in Text/HTML mode to paste a size
 * chart table (سایز، دور سینه، قد آستین، سرشانه، قد عبا, etc.) — replaces
 * the old lettered measuring-diagram field, which this project no longer uses.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$content = negarin_option( 'size_guide_content' );
?>
<div
	x-show="sizeChartOpen"
	x-cloak
	class="fixed inset-0 z-[60] flex items-end md:items-center justify-center"
>
	<div class="absolute inset-0 bg-black/50" @click="sizeChartOpen = false"></div>

	<div class="relative bg-white w-full md:max-w-2xl max-h-[85vh] overflow-y-auto rounded-t-2xl md:rounded-sm p-6 md:p-8 text-right">

		<div class="flex items-center justify-between pb-4 border-b border-negarin-line mb-6">
			<button @click="sizeChartOpen = false" aria-label="<?php esc_attr_e( 'بستن', 'negarin' ); ?>" class="text-2xl leading-none order-1">&times;</button>
			<h3 class="font-serif text-base md:text-xl order-2"><?php esc_html_e( 'راهنمای سایز', 'negarin' ); ?></h3>
		</div>

		<?php if ( $content ) : ?>
			<div class="prose prose-sm max-w-none negarin-size-chart">
				<?php echo wp_kses_post( $content ); ?>
			</div>
		<?php else : ?>
			<p class="text-sm opacity-70"><?php esc_html_e( 'جدول سایز به‌زودی اضافه می‌شود.', 'negarin' ); ?></p>
		<?php endif; ?>

		<button type="button" class="btn btn--outline w-full mt-6" @click="sizeChartOpen = false">
			<?php esc_html_e( 'بازگشت', 'negarin' ); ?>
		</button>
	</div>
</div>
