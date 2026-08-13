<?php
/**
 * "راهنمای سایز" — nested inside the custom-order modal, opened via
 * `sizeGuideOpen` (Alpine state declared in negarinCustomOrder()). Content
 * is a lettered illustration (A/B/C/D) + matching descriptions, fully
 * admin-managed from Theme Options -> Sizing Presets, matching the Figma
 * export exactly.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = negarin_option( 'size_guide_items', array() );
$image = negarin_option( 'size_guide_image' );
?>
<div x-show="sizeGuideOpen" x-cloak class="fixed inset-0 z-[60] flex items-end md:items-center justify-center">
	<div class="absolute inset-0 bg-black/60" @click="sizeGuideOpen = false"></div>

	<div class="relative bg-white w-full md:max-w-4xl max-h-[90vh] overflow-y-auto rounded-t-2xl md:rounded-sm p-6 md:p-10 text-right" @click.outside="sizeGuideOpen = false">

		<div class="flex items-center justify-between mb-8">
			<button @click="sizeGuideOpen = false" aria-label="<?php esc_attr_e( 'بستن', 'negarin' ); ?>" class="text-2xl leading-none order-1">&times;</button>
			<h4 class="font-serif text-xl order-2"><?php esc_html_e( 'راهنمای سایز', 'negarin' ); ?></h4>
		</div>

		<div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">

			<?php if ( $image ) : ?>
				<?php negarin_image( $image, 'negarin-section-half', 'w-full h-auto' ); ?>
			<?php endif; ?>

			<div class="space-y-6">
				<?php foreach ( $items as $item ) : ?>
					<div>
						<h5 class="flex items-center gap-2 font-medium mb-2">
							<span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
							<span><?php echo esc_html( $item['title'] ?? '' ); ?></span>
							<span class="text-emerald-600"><?php echo esc_html( $item['letter'] ?? '' ); ?></span>
						</h5>
						<p class="text-sm opacity-75 leading-7"><?php echo esc_html( $item['description'] ?? '' ); ?></p>
					</div>
				<?php endforeach; ?>

				<?php if ( ! $items ) : ?>
					<p class="opacity-60 text-sm"><?php esc_html_e( 'راهنمای اندازه‌گیری هنوز در تنظیمات قالب وارد نشده است.', 'negarin' ); ?></p>
				<?php endif; ?>
			</div>

		</div>
	</div>
</div>
