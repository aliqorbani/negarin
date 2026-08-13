<?php
/**
 * "سفارش شخصی" modal — two-column layout matching the Figma export:
 * measurement fields on the right (in RTL reading order), name/phone on
 * the left, a "راهنمای سایز" link, and a single dark submit button.
 *
 * Each measurement field still supports both a preset dropdown AND manual
 * entry (per the earlier decision) — the toggle is a small text link next
 * to the field label so it doesn't disturb the clean two-column layout.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Negarin\Services\CustomOrder;

$product = wc_get_product( get_the_ID() );

if ( ! $product ) {
	return;
}

$fields = CustomOrder::get_measurement_fields();
$guest  = ! is_user_logged_in();

// Guest contact fields, defined once so they can be interleaved with the
// measurement fields in the same two-column grid as the design shows.
$contact_fields = $guest
	? array(
		array(
			'name'        => 'name',
			'label'       => __( 'نام و نام خانوادگی', 'negarin' ),
			'type'        => 'text',
			'model'       => 'guestName',
		),
		array(
			'name'        => 'phone',
			'label'       => __( 'شماره تماس', 'negarin' ),
			'type'        => 'tel',
			'model'       => 'guestPhone',
		),
	)
	: array();
?>
<div
	x-show="customOrderOpen"
	x-cloak
	class="fixed inset-0 z-50 flex items-end md:items-center justify-center"
	x-data="negarinCustomOrder()"
>
	<div class="absolute inset-0 bg-black/50" @click="customOrderOpen = false"></div>

	<div class="relative bg-white w-full md:max-w-2xl max-h-[90vh] overflow-y-auto rounded-t-2xl md:rounded-sm p-6 md:p-8 text-right" @click.outside="customOrderOpen = false">

		<div class="flex items-center justify-between mb-6 pb-4 border-b border-black/10">
			<button @click="customOrderOpen = false" aria-label="<?php esc_attr_e( 'بستن', 'negarin' ); ?>" class="text-2xl leading-none order-1">&times;</button>
			<h3 class="font-serif text-xl order-2"><?php esc_html_e( 'سفارش شخصی', 'negarin' ); ?></h3>
		</div>

		<form method="post" action="<?php echo esc_url( $product->get_permalink() ); ?>" @submit="loading = true" class="space-y-6">
			<?php wp_nonce_field( 'negarin_custom_order', 'negarin_custom_order_nonce' ); ?>
			<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
			<input type="hidden" name="quantity" value="1">

			<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
				<?php
				$rows = max( count( $fields ), count( $contact_fields ) );
				for ( $i = 0; $i < $rows; $i++ ) :
					$field   = $fields[ $i ] ?? null;
					$contact = $contact_fields[ $i ] ?? null;
					?>
					<?php if ( $field ) : ?>
						<div class="measurement-field" x-data="{ mode: <?php echo $field['presets'] ? "'preset'" : "'manual'"; ?> }">
							<label class="flex items-center justify-between text-sm mb-2">
								<span><?php echo esc_html( $field['label'] ); ?></span>
								<?php if ( $field['presets'] ) : ?>
									<button type="button" class="text-xs text-negarin-gold underline" @click="mode = mode === 'preset' ? 'manual' : 'preset'">
										<span x-show="mode === 'preset'"><?php esc_html_e( 'ورود دستی', 'negarin' ); ?></span>
										<span x-show="mode === 'manual'"><?php esc_html_e( 'انتخاب از لیست', 'negarin' ); ?></span>
									</button>
								<?php endif; ?>
							</label>

							<?php if ( $field['presets'] ) : ?>
								<select x-show="mode === 'preset'" x-model="measurements.<?php echo esc_attr( $field['key'] ); ?>" class="w-full border border-black/15 rounded-sm px-4 py-3">
									<option value=""><?php esc_html_e( 'انتخاب کنید', 'negarin' ); ?></option>
									<?php foreach ( $field['presets'] as $preset ) : ?>
										<option value="<?php echo esc_attr( $preset['value'] ); ?>"><?php echo esc_html( $preset['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
							<?php endif; ?>

							<input
								x-show="mode === 'manual'"
								x-model="measurements.<?php echo esc_attr( $field['key'] ); ?>"
								type="number" step="0.5" min="1" max="300"
								placeholder="<?php esc_attr_e( 'اینجا وارد نمایید', 'negarin' ); ?>"
								class="w-full border border-black/15 rounded-sm px-4 py-3" dir="ltr"
							>
							<input type="hidden" name="measurements[<?php echo esc_attr( $field['key'] ); ?>]" :value="measurements.<?php echo esc_attr( $field['key'] ); ?>">
						</div>
					<?php else : ?>
						<div></div>
					<?php endif; ?>

					<?php if ( $contact ) : ?>
						<div>
							<label class="block text-sm mb-2" for="negarin-co-<?php echo esc_attr( $contact['name'] ); ?>"><?php echo esc_html( $contact['label'] ); ?></label>
							<input
								id="negarin-co-<?php echo esc_attr( $contact['name'] ); ?>"
								type="<?php echo esc_attr( $contact['type'] ); ?>"
								name="<?php echo esc_attr( $contact['name'] ); ?>"
								x-model="<?php echo esc_attr( $contact['model'] ); ?>"
								placeholder="<?php esc_attr_e( 'اینجا وارد نمایید', 'negarin' ); ?>"
								class="w-full border border-black/15 rounded-sm px-4 py-3"
								<?php echo 'tel' === $contact['type'] ? 'dir="ltr"' : ''; ?>
							>
						</div>
					<?php else : ?>
						<div></div>
					<?php endif; ?>
				<?php endfor; ?>
			</div>

			<div>
				<label class="block text-sm mb-2" for="negarin-co-note"><?php esc_html_e( 'توضیحات (اختیاری)', 'negarin' ); ?></label>
				<textarea id="negarin-co-note" name="note" rows="2" class="w-full border border-black/15 rounded-sm px-4 py-3"></textarea>
			</div>

			<div>
				<button type="button" class="text-sm text-negarin-gold underline" @click="sizeGuideOpen = true">
					<?php esc_html_e( 'راهنمای سایز', 'negarin' ); ?>
				</button>
			</div>

			<p class="text-red-600 text-sm" x-show="error" x-text="error"></p>

			<button type="submit" class="btn btn--solid w-full" :disabled="loading">
				<span x-show="!loading"><?php esc_html_e( 'ثبت و ادامه سفارش', 'negarin' ); ?></span>
				<span x-show="loading"><?php esc_html_e( 'در حال ثبت...', 'negarin' ); ?></span>
			</button>
		</form>
	</div>

	<?php get_template_part( 'template-parts/components/size-guide-modal' ); ?>
</div>
