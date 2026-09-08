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

    <div class="container max-w-7xl mx-auto px-4 py-8">

        <?php if ( WC()->cart->is_empty() ) : ?>

            <div class="text-center py-24">
                <p class="mb-6 opacity-70"><?php esc_html_e( 'سبد خرید شما خالی است.', 'negarin' ); ?></p>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn--solid">
                    <?php esc_html_e( 'مشاهده محصولات', 'negarin' ); ?>
                </a>
            </div>

        <?php else : ?>

            <form class="woocommerce-cart-form grid grid-cols-1 md:grid-cols-[1fr_320px] gap-8 items-start" method="post" action="<?php echo esc_url( wc_get_cart_url() ); ?>">
                <?php do_action( 'woocommerce_before_cart_table' ); ?>

                <div class="order-1">
                    <div class="overflow-x-auto border border-negarin-line p-5 pb-0">
                        <table class="w-full text-sm text-right mt-0 mb-0">
                            <thead>
                            <tr class="bg-[#f0f1f2] text-xs">
                                <th class="py-3 px-3 font-normal w-full"><?php esc_html_e( 'محصول', 'negarin' ); ?></th>
                                <th class="py-3 px-3 font-normal"><?php esc_html_e( 'تعداد', 'negarin' ); ?></th>
                                <th class="py-3 px-3 font-normal"><?php esc_html_e( 'قیمت تک', 'negarin' ); ?></th>
                                <th class="py-3 px-3 font-normal"></th>
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
                                        <div class="flex justify-start items-center gap-3">
                                            <?php if ( $permalink ) : ?>
                                                <a href="<?php echo esc_url( $permalink ); ?>" class="shrink-0 w-17 h-32.5 block overflow-hidden">
                                                    <?php echo $product->get_image( 'thumbnail', array( 'class' => 'w-full h-full object-cover' ) ); // phpcs:ignore ?>
                                                </a>
                                            <?php endif; ?>
                                            <div class="flex flex-col">
                                                <a href="<?php echo esc_url( $permalink ); ?>" class="block"><?php echo wp_kses_post( $product->get_name() ); ?></a>
                                                <?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
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
                                    <td class="align-middle whitespace-nowrap">
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

                <div class="order-2 md:sticky md:top-24">
                    <div class="bg-white p-4 text-right border border-negarin-line">
                        <h2 class="font-medium font-serif mb-4 mt-0.5 pr-2 text-base"><?php esc_html_e( 'فاکتور شما', 'negarin' ); ?></h2>

                        <?php get_template_part( 'template-parts/components/order-totals-rows' ); ?>

                        <div class="text-sm bg-white rounded-sm px-4 py-3 my-4 flex items-center gap-2">
                            <span>🎁</span>
                            <span><?php esc_html_e( 'ارسال رو مهمان نگارین هستید :)', 'negarin' ); ?></span>
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
                    $negarin_terms_url  = $negarin_terms_page ? get_permalink( $negarin_terms_page ) : '';
                    ?>
                    <?php if ( $negarin_terms_url ) : ?>
                        <a href="<?php echo esc_url( $negarin_terms_url ); ?>" class="border border-negarin-line flex items-center justify-center gap-2 px-4 py-3 mt-4 text-sm">
                            <span><?php esc_html_e( 'شرایطی که قبل از ثبت سفارش باید بخوانید', 'negarin' ); ?></span>
                            <span>💌</span>
                        </a>
                    <?php endif; ?>
                </div>

                <?php do_action( 'woocommerce_after_cart_table' ); ?>
            </form>

        <?php endif; ?>
    </div>

<?php do_action( 'woocommerce_after_cart' ); ?>