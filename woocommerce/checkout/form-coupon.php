<?php
/**
 * Checkout coupon form. WooCommerce's own checkout.js listens for a submit
 * on `form.checkout_coupon` with a `coupon_code` input and `apply_coupon`
 * button and handles it over AJAX — none of that is reimplemented here,
 * only the open/close interaction and styling (Alpine instead of WC's
 * default `.showcoupon` class + jQuery slideToggle).
 *
 * Previously rendered at the very top of the checkout page (WooCommerce
 * hooks this to `woocommerce_before_checkout_form` by default) — moved
 * into checkout/review-order.php instead, right above the order table,
 * via a `remove_action()` in inc/hooks/woocommerce.php.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! wc_coupons_enabled() ) { // @codingStandardsIgnoreLine.
	return;
}
?>
<div x-data="{ couponOpen: false }" class="mb-4 text-right">
	<button type="button" class="flex items-center justify-between w-full text-sm py-2" @click="couponOpen = !couponOpen">
		<span><?php esc_html_e( 'اضافه کردن کوپن‌های تخفیف', 'negarin' ); ?></span>
		<span x-text="couponOpen ? '−' : '+'" class="text-lg leading-none"></span>
	</button>

	<form class="checkout_coupon woocommerce-form-coupon flex gap-2 mt-2" method="post" x-show="couponOpen" x-cloak>
		<label for="coupon_code" class="sr-only"><?php esc_html_e( 'کد تخفیف', 'negarin' ); ?></label>
		<input
			type="text"
			name="coupon_code"
			id="coupon_code"
			value=""
			class="flex-1 border border-negarin-line rounded-sm px-3 py-2 text-sm"
			placeholder="<?php esc_attr_e( 'کد را وارد کنید', 'negarin' ); ?>"
		/>
		<button type="submit" class="btn btn--outline px-5" name="apply_coupon" value="<?php esc_attr_e( 'اعمال کردن', 'negarin' ); ?>">
			<?php esc_html_e( 'اعمال کردن', 'negarin' ); ?>
		</button>
	</form>
</div>
