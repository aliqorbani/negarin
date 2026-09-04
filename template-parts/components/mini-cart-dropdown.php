<?php
/**
 * Mini-cart hover dropdown. No Figma design exists for this — per Ali's
 * direction, kept deliberately minimal: thumbnail, title, size, price, qty
 * per line, then "مشاهده سبد خرید" / "تسویه‌حساب" actions.
 *
 * Included once in template-parts/header/site-header.php, inside the same
 * `relative` wrapper as the cart icon link. Shown on hover on desktop only
 * (`md:group-hover:block` — pure CSS, no JS) via the `group` class on that
 * wrapper; never shown on mobile/touch, where tapping the cart icon just
 * navigates to the cart page as before.
 *
 * Always rendered (not conditionally, even when empty) and refreshed via
 * the `#negarin-mini-cart` entry in `woocommerce_add_to_cart_fragments`
 * (inc/hooks/woocommerce.php) — same reasoning as cart-drawer-count.php:
 * outerHTML fragment replacement needs the node to already exist.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}
?>
<div id="negarin-mini-cart" class="hidden md:group-hover:block absolute -right-9 top-full pt-3 z-50 w-80">
    <div class="bg-white border border-negarin-line rounded-sm shadow-xl p-4 text-right">
        <?php if ( WC()->cart->is_empty() ) : ?>
            <p class="text-sm opacity-70 py-2"><?php esc_html_e( 'سبد خرید شما خالی است.', 'negarin' ); ?></p>
        <?php else : ?>
            <div class="flex flex-col gap-3 max-h-80 overflow-y-auto">
                <?php foreach ( WC()->cart->get_cart() as $cart_item ) : ?>
                    <?php
                    $product     = $cart_item['data'];
                    $size_label  = negarin_cart_item_size_label( $cart_item, $product );
                    ?>
                    <div class="flex items-center gap-3">
                        <?php echo wp_kses_post( $product->get_image( 'thumbnail', array( 'class' => 'w-12 h-12 object-cover rounded-sm shrink-0' ) ) ); ?>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm truncate"><?php echo esc_html( $product->get_name() ); ?></p>
                            <?php if ( $size_label ) : ?>
                                <p class="text-xs opacity-60"><?php echo esc_html( $size_label ); ?></p>
                            <?php endif; ?>
                            <p class="text-xs opacity-80 mt-0.5">
                                <?php echo wp_kses_post( WC()->cart->get_product_price( $product ) ); ?>
                                &times; <?php echo esc_html( $cart_item['quantity'] ); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center justify-between border-t border-negarin-line mt-4 pt-3 text-sm">
                <span><?php esc_html_e( 'جمع سبد خرید', 'negarin' ); ?></span>
                <span><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>
            </div>

            <div class="flex flex-col gap-2 mt-4">
                <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn btn--solid w-full text-center">
                    <?php esc_html_e( 'تسویه‌حساب', 'negarin' ); ?>
                </a>
                <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="btn btn--outline w-full text-center">
                    <?php esc_html_e( 'مشاهده سبد خرید', 'negarin' ); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>