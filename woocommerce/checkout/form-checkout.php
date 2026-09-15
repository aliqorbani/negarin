<?php
/**
 * WooCommerce checkout override.
 *
 * IMPORTANT: this is still a single <form name="checkout"> — exactly what
 * WooCommerce's core checkout.js expects (AJAX order review, payment
 * gateway toggling, validation, `#place_order` submit). We only wrap two
 * halves of it in Alpine `x-show` panels so it *looks* like two screens;
 * nothing about WooCommerce's own checkout processing is reimplemented.
 * This keeps every payment gateway plugin compatible with zero extra work.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_ajax() ) {
    do_action( 'woocommerce_before_checkout_form', $checkout );

    if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
        echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'برای تکمیل خرید باید وارد حساب کاربری خود شوید.', 'negarin' ) ) );
        return;
    }
}
?>

    <div class="container max-w-7xl mx-auto px-4 pt-8">
    <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" x-data="negarinCheckoutForm">

        <?php if ( $checkout->get_checkout_fields() ) : ?>
            <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

            <div class="container max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-[1fr_320px] gap-8 items-start">

                <div class="order-1">

                    <!-- Step 1: address -->
                    <div x-show="step === 1" x-cloak id="customer_details">
                        <?php do_action( 'woocommerce_checkout_billing' ); ?>
                    </div>

                    <!-- Step 2: payment -->
                    <div x-show="step === 2" x-cloak id="order_review" class="woocommerce-checkout-review-order">
                        <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                    </div>

                </div>

                <div class="order-2 md:sticky md:top-24">
                    <div class="bg-white p-4 text-right border border-negarin-line">
                        <h2 class="font-serif text-lg mb-4 mt-0.5"><?php esc_html_e( 'فاکتور شما', 'negarin' ); ?></h2>

                        <div id="negarin-order-totals">
                            <?php get_template_part( 'template-parts/components/order-totals-rows' ); ?>
                        </div>

                        <div class="text-sm bg-white rounded-sm px-4 py-3 my-4 flex items-center gap-2">
                            <span>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><rect width="24" height="24" fill="url(#pattern0_217_333)"/><defs><pattern id="pattern0_217_333" patternContentUnits="objectBoundingBox" width="1" height="1"><use xlink:href="#image0_217_333" transform="scale(0.0138889)"/></pattern><image id="image0_217_333" width="72" height="72" preserveAspectRatio="none" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEgAAABICAMAAABiM0N1AAAC91BMVEUAAADVra/ClnqtdQ7Eooq/immzehC+jj25i0S8k2TTJSa+kFPtSknIViC/izWmezGYTA+8iTnZVle9llqsTBaTYwzXmBq3gyvWY2S6gB+sfzjRkBusdxejchvMjye3fyPEiSrDgRinISDcNTqyexTUlyDHJyjSlyTKkTHSODnvV1i2QkG9kUy9j0q8fRm1fhSuLCuseh/yUlTAdBbuzR6/gB31TUzJih2wMTHyXFehcSu3SjvNQz/FXjLUJyenLyDAgyDIiB/TMDHZNzqreyrOlC7XR0r8VFX1TE72UVHr0T25fw6nICCnIiC4fg7ZKCi9gg/oqRm3fQ7hohm7gA7fMDHaKirmoxnFghi+gw+oISDjpRneNjznphnhpBneLy7joxnhNz3mqBm1fA3gnxnbmBf8V1XcnRbYJCW0eg5fSAD1RkjXMjjroxncKyzqqRnKhhjChg/aNTv3SUrtQEPkOj+ydw78U1HnPECfIhzeihnAfxb6TU2nJx7HhRfNKizRlRPxQ0a3JCOaIhmxcQ/2XVjNGRntrRjqPUHcMjfRMjajKxx3FxKrcA37XFj6UVDrnhltGhnHLzHUGxzfmhnflBizaxK3ZBJVEQ/QeBn0vBjakBjCGRjQiheSGhTUmhNoExOxXxJRCQlACQn9YF3jRETfOz7CJyWtIyKKIB787hzRbBr2hhjXehjiqBeuFheCGhbJjBJeDw+8KivNIiH++B3HexroiBj3fBirVxGgWQ6lbw34ZmLyUU+pMR754BzbhRm5GRnGaBhLDQ6PRQ1GBwh4Ix7efRnpthjwtBejFhY6CAhoTQH2V1KtODTULS/KRCWYJiTZNyLebxuqPhrIchnnrxeFPgt7NgtoIAfSNirbRSLGQh71zRvvdheFXQh4VQQ6BATqXEHWPjzlNjbdPTK2LC/BMSTcYx/y1hvmmhnmcxfDiBSoZg6iYw5gPQT+b2nlYzvbWSDaTSD4xRm2QBiJLxBwOQvbUSG3Shm5exbaXVn3mD3Dqh1tAAAAS3RSTlMABBD9Chj1Xksd/DL+/nxx/W5CKP7+9aAoylvv2ca7u6v59O/i4dvNjIBtXUA89eq/tKD99ufl4qSHh3hp8uHe19fDsJ2afNnRv2OjkKk0AAAJoElEQVRYw6XYd1hTVxgG8CQE2Qg4AGmr1rZaqx127z0DEZLIMBCCJIQYBCQQEJAwDMsEBEJZsrdUZDiqgIAoOHFVi7tu627V2vVHv3PuvdwYalPbV5+H9fDj/c495zwExpiYz543b95sJxb9Gaa5pbOzBQu/6+I028mKyRgT5tjPvKlURGfGzPjYifwaa6696/MJCc9PncJiuLz5hW3MjI+eYzJMx+W1REV0XEyQ/4zP2AwG28Z+6qsJCb4QoN78PFGZkhm0/xmnfwHNTkxMyQQISTaO05BCZuNPubG58FNishrfNu2wvoxVokIARc14Fym0ExISG4vqZqnfMD2b02uxKZkxqJC/f1T2yW2GDoJgtjh/9Yss05OF5KbgwRAUpl5/8sjGjRuRcy4EBWbDi+RiymHOC0mMjgGGgLLV3hcvXjx+wK7lXG4slmC2zKDs1GeedfnH6Vhvv5ajzCScBiRFKWBNgrLU2T0IysnJCYElBGj9eqAeprBtHKe+bFunjIN1joryb2ho8A+zzVWmwMdh+ba52KEhoN5h/d1MEyymvTrdt2RfnSIOnLCwqIaY+piGfbGJsPYY2pdTV1dHQampTR4rHd4yN1asLJw/mO6blJQEkDLOHxwkxcXUa9DzRlB9zz6NJodYI/9sD23esftnzkx6fLwhZW45CymRkZEAaepgjTCU5R8U1EBBnQ3RKT2JubmJxOOvKL61ePHevT8gyopama9mukUSScJQInxjFjgIqu/uzsm1jfPPzo+Ly0xRKCGK6KCo/F8Wo+z9YVKX2ThXR4KyeWXNKXFZOQH5btTUxaLVzUJOWOel7tvdOT31Udn1iujoFIVCqVCkxEG/+wR0ZpLZ73CcCWrO0z9uWFOqL8cOQLdDlGhn+2dfau6oHTiv0fTUn89eNjhcr8BJgcGzl9+iCv2Oz9B0Vws2Y8LT0p29G3qPQqkk34SXz92uy42O6jyoLa7ds2fPjorjF88fv5yqPXu2uDnfVqlMgblHJztzU5UAa1teJomYbMGwminT819Zs+EUvzwyYdvJ+j+GD2o7dpw9e3ZPbe3AQLG2Ii2tQltT29FRs6N4cPh8ZkxUWOfgLaKQWQIw+ohdRyv97BnmM2USib4V5tvZckCt7tTW1OyA1NbuGCguLu6oqioegG61Hc0VqOTA4KWobI+8vPu3FiOnvMxn167WZD+O3J7BegIgyM7eP22z9qsbl2k7ampqBmrAyGur2L27raa2pgM87cGtK5uragaKmy815+XlDY5MkkdKoYys3ZPD5crHM9gI4vP5Ev05jaY+TN3Y1FZVVZWnBaR/dX9F1bGR7dvTdq9bt273suHh4YPNbW1tyGluutySAWWWcrgcDoc7zpHBniYDB6I/caGnu6dhf2PqOkj/6vT0/grtiEN1UVFR9b3VKP3rmjxAyqvKGzyYqlZfLocyEBJifirl40hOhC+I1nTbBhYUFKSnFwDzi0NRC8o2yMn16ekYg2q705ry8zubln8NBAVZMBizCEgsuRMeuOCCbbfmQgGkf6VD9ZGStddOnz597drhwyVHEEXGWp0PZ9b9AWgKg2EvBQXC/zU8MHDBgrh93T0LGu+BUlKyduhbnNPXhtaWHLE7HloAsVZ3pi70gBhDlm7gQEQ/QyPIhWjNu5VXh9ZC+jZ9A0HSEPq4xe54+H6YCZAxkCXcIRavyxAkvIIbQYKOrFlzakl8X19fPEgom+LR+6sySmCmhaTjbgSB5CxI5gsNoCy7o7A/7/qUxcfHb0KBt4VXM0pLWxO2LkQODQlI6CkbBmRW8vXrUmHrYYACCUgs3tkLR/kqorCypLR0Z2W734qV2KEhkAQCjuqx8WwMSSXJ19tFJASxEwb4LILz15tRWFZWVrhqF5RJXuopEBhDAghP1dU1HjHEc5O1X2+hGwm9vAJ80HynMpZkQBlZu58nylgo2Kyry0xlQUAWUhFspMpt3oGkZCfy8kIUul/u3j0KZSAkZLhGXJWuS6cKDh5HQZNFIhFf1OcdTkF8cBDldap3F80YN7oBZeTBPB4PnhmO5WSxUCgSxc+npAMA4UQElFYu9fN7CHQTM1wOl4LmTBYJIYWHQCIgCQUtyUimIePRVmCGQ0M2bhjSH5oPUnggQLJ/gCjJfesK5GBoDnbg2hYjiH8idL63N5QyhihGYASpwMF5YS4BWT3NR5Do11CoBAm8HPnwRkDQEOdByHwmX4SgnykodeRliQFESQLeDQS5e7ij/+7fybkUZIMduLYlCGq9QkLhqcc2+yYLIzBUSUGCYN2W79MWgoAZgIKNITZAIoAOA4So8MZjRdL2JJlXBAmBxFHpdLotIwCBgjEa4j41gYCY02QAiVqHDCC+qLK9XExCnjwznU4ll98cGW0EE07kcYyhT2V8Ed6RBOStPlYkbl0kSk6SIshTrtOZyWEDB9/Ao1ExhiCzAIIUHqIgbZFYuGiRUNZeLm0HRoWH4PJoCJXaTD193mNWDPr4A6U/FEpA1gQEVGWCXCXncdB9AZAOIA9ysQHiUms0CjkCBJLkBECI2t9WBA5KgCTZz1OAAz9ZRzeCRaqmIJ6rOYM6/nycO6HkbBjC53+JDB9+LHFXUI8fVQIIS9DUlUVCliQEO5KQmosCAEIByI+GtlONDCAulzfVCBJdoaCKakMIGAJS0RC61zDERRCbhOa6STAEOxInPI2CIkxBXEjwSwCRx5+Ahiho2YOQ5yi0jIQ8AEK3CA4NwS9bGOqbH0pAy6uFYyCYQm4IbaUhZyYJMe3dZAiKp6Cmzejx06MZQeQtAhCPhnDYFh9KQSo8RK52qiEEDgU5jDZyJyAUnhxBVGymTZbw9TQkDggIMITwhgmmIXQdIQgdQbPxDIOwHF+Xjm7txs1iYQAEICmajIMdGgKJguRmjxm9tmHOfSL5jiGEgiHqkPNoCN9rUAYxNswxfzqwt6Mhn7+DtpCQB/ybGIyY8ROMGVzqHWvitKkn8klpldRPQEI8I0huRr80Mg7rWUKyRpCPDw0Rj4eHIOrQTnx8ijnjoWG/814ohiQAQQwhHgXhPPk2y8QL/4+hlPVvhhAwNITGAubF59gMEzF/9r1QgHyIAMR5EIIAA1OZDNPpI+t7UgOIZCA3ATJkTJd6f7peTEOg4C0MT839yTeeoxfHdCmXKa5lEkMIAtvG4f03nIB5pExwdtMjqJCEglWwbSxdmI9A0DcClMIQyVg9KkHfCOV6BAEz7iXYff89cCMUFvrxEMNi/K8w5zwRKX/qE0s2w3RMrJ6V/SdzgDGdvwAyplYV/S+TvQAAAABJRU5ErkJggg=="/></defs></svg>
                            </span>
                            <span><?php esc_html_e( 'ارسال رو مهمان نگارین هستید :‌)', 'negarin' ); ?></span>
                        </div>

                        <?php wc_get_template( 'checkout/form-coupon.php' ); ?>

                        <button type="button" x-show="step === 1" class="btn btn--solid w-full" @click="goToPayment()">
                            <?php esc_html_e( 'تایید و ادامه', 'negarin' ); ?>
                        </button>
                        <!-- Step 2's real submit button is WooCommerce's own #place_order,
                             rendered inside woocommerce_checkout_payment via checkout/payment.php.
                             assets/js/checkout.js physically moves it here (Figma has the
                             button in the sidebar for both steps) and re-runs that move after
                             every `updated_checkout` AJAX refresh, since WooCommerce replaces
                             #order_review's whole markup on every totals/gateway change. -->
                        <div id="negarin-place-order-slot" x-show="step === 2" x-cloak></div>
                    </div>
                    <?php
                    $negarin_terms_page = negarin_option( 'checkout_terms_page' );
                    //                    echo urldecode($negarin_terms_page);
                    //                    $negarin_terms_url  = $negarin_terms_page ? get_permalink( $negarin_terms_page ) : '';
                    //this has been removed because option return type is a url not a page_id or something els
                    if ( $negarin_terms_page ) : ?>
                        <a href="<?php echo esc_url( $negarin_terms_page ); ?>" class="border border-negarin-line flex items-center justify-center gap-2 px-4 py-3 mt-4 text-sm">
                            <span>💌</span>
                            <span><?php esc_html_e( 'شرایطی که قبل از ثبت سفارش باید بخوانید', 'negarin' ); ?></span>
                        </a>
                    <?php endif; ?>
                </div>

            </div>

            <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
        <?php endif; ?>

    </form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>