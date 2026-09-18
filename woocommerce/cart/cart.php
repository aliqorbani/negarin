<?php
/**
 * WooCommerce cart page override.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_before_cart' );
?>

    <div class="container max-w-7xl mx-auto md:px-4 md:py-8 px-3">

        <?php if ( WC()->cart->is_empty() ) : ?>

            <div class="text-center py-24">
                <p class="mb-6 opacity-70"><?php esc_html_e( 'سبد خرید شما خالی است.', 'negarin' ); ?></p>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn--solid">
                    <?php esc_html_e( 'مشاهده محصولات', 'negarin' ); ?>
                </a>
            </div>

        <?php else : ?>

            <form class="woocommerce-cart-form grid grid-cols-1 md:grid-cols-[1fr_280px] gap-8 items-start" method="post" action="<?php echo esc_url( wc_get_cart_url() ); ?>">
                <?php do_action( 'woocommerce_before_cart_table' ); ?>

                <div class="order-1">

                    <!-- Mobile cart cards — Figma 165:422 "Cart_mobile" -->
                    <div class="md:hidden">
                        <?php
                        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                            get_template_part(
                                    'template-parts/components/cart-item-card',
                                    null,
                                    array(
                                            'cart_item'     => $cart_item,
                                            'cart_item_key' => $cart_item_key,
                                    )
                            );
                        }
                        ?>
                    </div>

                    <div class="hidden md:block overflow-x-auto border border-negarin-line p-5 pb-0">
                        <table class="w-full text-right mt-0 mb-0">
                            <thead>
                            <tr class="bg-[#F0F1F2] text-base">
                                <th class="pt-3.5 pb-4 px-3 font-normal"><?php esc_html_e( 'محصول', 'negarin' ); ?></th>
                                <th class="pt-3.5 pb-4 px-3 font-normal text-center"><?php esc_html_e( 'تعداد', 'negarin' ); ?></th>
                                <th class="pt-3.5 pb-4 px-3 font-normal"><?php esc_html_e( 'قیمت تک', 'negarin' ); ?></th>
                                <th class="pt-3.5 pb-4 px-3 font-normal"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
                                $product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                                $permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

                                if ( ! $product || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                                    continue;
                                }
                                ?>
                                <tr class="border-b border-black/5">
                                    <td class="align-middle mt-4 mb-4">
                                        <div class="flex gap-3 items-center justify-start pb-4 pt-4">
                                            <?php if ( $permalink ) : ?>
                                                <a href="<?php echo esc_url( $permalink ); ?>" class="shrink-0 w-17 h-32.5 block overflow-hidden">
                                                    <?php echo $product->get_image( 'thumbnail', array( 'class' => 'w-full h-full object-cover' ) ); // phpcs:ignore ?>
                                                </a>
                                            <?php endif; ?>
                                            <div class="flex flex-col">
                                                <a href="<?php echo esc_url( $permalink ); ?>" class="block text-lg font-semibold"><?php echo wp_kses_post( $product->get_name() ); ?></a>
                                                <?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle mx-auto text-center">
                                        <?php
                                        if ( $product->is_sold_individually() ) {
                                            echo '1';
                                        } else {
                                            get_template_part(
                                                    'template-parts/components/quantity-stepper',
                                                    null,
                                                    array(
                                                            'product'       => $product,
                                                            'cart_item_key' => $cart_item_key,
                                                            'quantity'      => $cart_item['quantity'],
                                                    )
                                            );
                                        }
                                        ?>
                                    </td>
                                    <td class="align-middle font-semibold text-lg whitespace-nowrap">
                                        <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $product ), $cart_item, $cart_item_key ); // phpcs:ignore ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php
                                        echo apply_filters( // phpcs:ignore
                                                'woocommerce_cart_item_remove_link',
                                                sprintf(
                                                        '<a href="%s" class="remove text-negarin-red" aria-label="%s" data-product_id="%s" data-cart_item_key="%s">%s</a>',
                                                        esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                                        esc_attr__( 'حذف از سبد خرید', 'negarin' ),
                                                        esc_attr( $product->get_id() ),
                                                        esc_attr( $cart_item_key ),
                                                        '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6m2 0v13a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V6h10ZM10 10.5v6M14 10.5v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                                                ),
                                                $cart_item_key
                                        );
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                        <button type="submit" name="update_cart" value="<?php esc_attr_e( 'بروزرسانی سبد خرید', 'negarin' ); ?>">
                            <?php esc_html_e( 'بروزرسانی سبد خرید', 'negarin' ); ?>
                        </button>
                        <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                    </div>
                </div>

                <div class="order-2 md:sticky md:top-24"><!--sticky bottom-0-->
                    <div class="bg-white py-4 px-2.5 text-right border border-negarin-line">
                        <h2 class="font-medium font-serif mb-4 mt-0.5 pr-2 text-base"><?php esc_html_e( 'فاکتور شما', 'negarin' ); ?></h2>

                        <div id="negarin-order-totals">
                            <?php get_template_part( 'template-parts/components/order-totals-rows' ); ?>
                        </div>

                        <div class="text-sm bg-white rounded-sm px-4 py-3 my-4 flex items-center gap-2">
                            <span>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><rect width="24" height="24" fill="url(#pattern0_217_333)"/><defs><pattern id="pattern0_217_333" patternContentUnits="objectBoundingBox" width="1" height="1"><use xlink:href="#image0_217_333" transform="scale(0.0138889)"/></pattern><image id="image0_217_333" width="72" height="72" preserveAspectRatio="none" xlink:href="data:image/png;base64,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"/></defs></svg>
                            </span>
                            <span><?php esc_html_e( 'ارسال رو مهمان نگارین هستید :‌)', 'negarin' ); ?></span>
                        </div>
                        <?php if ( wc_coupons_enabled() ) : ?>
                            <div x-data="{ couponOpen: false }" class="mb-4 text-right">
                                <button type="button" class="flex items-center justify-between w-full text-sm py-2" @click="couponOpen = !couponOpen">
                                    <span><?php esc_html_e( 'اضافه کردن کوپن‌های تخفیف', 'negarin' ); ?></span>
                                    <span x-text="couponOpen ? '−' : '+'" class="text-lg leading-none"></span>
                                </button>
                                <div class="coupon flex gap-2 mt-2" x-show="couponOpen" x-cloak>
                                    <label for="coupon_code" class="sr-only"><?php esc_html_e( 'کد تخفیف', 'negarin' ); ?></label>
                                    <input type="text" name="coupon_code" id="coupon_code" value="" class="flex-1 border border-negarin-line rounded-sm px-3 py-2 text-sm" placeholder="<?php esc_attr_e( 'کد را وارد کنید', 'negarin' ); ?>" />
                                    <button type="submit" class="btn btn--outline px-5" name="apply_coupon" value="<?php esc_attr_e( 'اعمال کردن', 'negarin' ); ?>">
                                        <?php esc_html_e( 'اعمال', 'negarin' ); ?>
                                    </button>
                                    <?php do_action( 'woocommerce_cart_coupon' ); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php //wc_get_template( 'checkout/form-coupon.php' ); ?>

                        <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn btn--solid w-full hover:text-white">
                            <?php esc_html_e( 'تایید و ادامه', 'negarin' ); ?>
                        </a>
                    </div>

                    <?php
                    $negarin_terms_page = negarin_option( 'checkout_terms_page' );
//                    echo urldecode($negarin_terms_page);
//                    $negarin_terms_url  = $negarin_terms_page ? get_permalink( $negarin_terms_page ) : '';
                    //this has been removed because option return type is a url not a page_id or something els
                    if ( $negarin_terms_page ) : ?>
                        <a href="<?php echo esc_url( $negarin_terms_page ); ?>" class="border border-negarin-line flex items-center justify-center gap-2 px-1.5 py-3 mt-4 text-sm">
                            <span>💌</span>
                            <span><?php esc_html_e( 'شرایطی که قبل از ثبت سفارش باید بخوانید', 'negarin' ); ?></span>
                        </a>
                    <?php endif; ?>
                </div>

                <?php do_action( 'woocommerce_after_cart_table' ); ?>
            </form>

        <?php endif; ?>
    </div>

<?php do_action( 'woocommerce_after_cart' ); ?>