<?php
/**
 * "سفارش شخصی" — stacks on top of size-select-modal.php (z-60 over its
 * z-50) via `customOrderOpen`, which lives on the ancestor x-data in
 * woocommerce/content-single-product.php. Only ever rendered/opened for a
 * logged-in shopper — see size-select-modal.php's `goToCustomOrder()` for
 * the guest redirect-to-login handoff, and Services/CustomOrder.php for
 * the server-side guard that backs it up.
 *
 * Collects exactly four measurements (fixed on purpose, see
 * Services/CustomOrder.php docblock) and submits over AJAX so a
 * successful add-to-cart can close this + the size-select modal and show
 * a toast, instead of reloading the page.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Negarin\Services\CustomOrder;

global $product;

$fields           = CustomOrder::get_measurement_fields();
$component_state  = wp_json_encode( array( 'productId' => $product->get_id() ) );
?>
<div x-show="customOrderOpen" x-cloak class="fixed inset-0 z-[60] flex items-end md:items-center justify-center">
	<div class="absolute inset-0 bg-black/50" @click="customOrderOpen = false"></div>

	<div
		x-data="negarinCustomOrder(<?php echo esc_attr( $component_state ); ?>)"
		class="relative bg-white w-full md:max-w-md max-h-[90vh] overflow-y-auto rounded-t-2xl md:rounded-sm p-6 md:p-8 text-right"
	>
		<div class="flex items-center justify-between pb-4 border-b border-negarin-line mb-6">
			<button @click="customOrderOpen = false" aria-label="<?php esc_attr_e( 'بستن', 'negarin' ); ?>" class="text-2xl leading-none order-1">&times;</button>
			<h3 class="font-serif text-base md:text-xl order-2"><?php esc_html_e( 'سفارش شخصی', 'negarin' ); ?></h3>
		</div>

		<div class="space-y-4 mb-6">
			<?php foreach ( $fields as $field ) : ?>
				<div>
					<label class="block text-sm mb-2" for="negarin-measurement-<?php echo esc_attr( $field['key'] ); ?>">
						<?php echo esc_html( $field['label'] ); ?>
						<span class="opacity-60">(<?php echo esc_html( $field['unit'] ); ?>)</span>
					</label>
					<input
						type="number"
						inputmode="decimal"
						min="1"
						max="300"
						step="0.5"
						id="negarin-measurement-<?php echo esc_attr( $field['key'] ); ?>"
						class="w-full border border-black/15 rounded-sm px-4 py-3"
						x-model="measurements.<?php echo esc_attr( $field['key'] ); ?>"
					>
				</div>
			<?php endforeach; ?>
		</div>

		<p class="text-negarin-red text-sm mb-4" x-show="error" x-text="error"></p>

		<button type="button" class="btn btn--solid w-full" :disabled="loading" @click="submit()">
			<span x-show="!loading"><?php esc_html_e( 'ثبت و ادامه سفارش', 'negarin' ); ?></span>
			<span x-show="loading"><?php esc_html_e( 'در حال ثبت...', 'negarin' ); ?></span>
		</button>
	</div>
</div>
