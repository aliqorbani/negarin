<?php
/**
 * Default page template (used when a Page does NOT use the "Page Builder" template).
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$is_bare_login_screen = function_exists( 'is_account_page' )
        && is_account_page()
        && ! is_user_logged_in();
$is_checkout_page     = function_exists( 'is_checkout' ) && is_checkout();
$need_article_border = (! is_woocommerce()
        && ! is_checkout()
        && ! is_account_page()
        && ! is_wc_endpoint_url()
//        && ! is_page(wc_get_page_id( 'myaccount' ))
        );
$post_classes[] = $need_article_border ? 'md:border' : '';
$post_classes[] = 'px-3 md:pt-10 md:px-6';
get_header();

?>
    <main id="main-content" class="container max-w-7xl mx-auto md:px-4 md:py-4">
        <?php
        while ( have_posts() ) :
            the_post();
            ?>

            <article <?php post_class($post_classes); ?>>

                <?php if ( ! $is_bare_login_screen && ! $is_checkout_page ) : ?>
                    <h1 class="font-serif md:text-2xl mb-10 text-center font-semibold text-lg">
                        <?php the_title(); ?>
                    </h1>
                <?php endif; ?>

                <div class="prose max-w-none leading-8 prose-a:no-underline prose-p:text-lg">
                    <?php the_content(); ?>
                </div>

            </article>

        <?php endwhile; ?>
    </main>
<?php
get_footer();