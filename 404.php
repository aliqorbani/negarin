<?php
/**
 * 404 template.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
    <main id="main-content" class="container max-w-2xl mx-auto px-4 py-20 md:py-28 text-center">
        <p class="font-serif text-6xl md:text-8xl mb-6 opacity-20">۴۰۴</p>
        <h1 class="font-serif text-xl md:text-2xl mb-4"><?php esc_html_e( 'صفحه مورد نظر پیدا نشد', 'negarin' ); ?></h1>
        <p class="opacity-70 mb-10"><?php esc_html_e( 'ممکن است این صفحه حذف شده یا آدرس آن اشتباه وارد شده باشد.', 'negarin' ); ?></p>

        <div class="max-w-sm mx-auto mb-10">
            <?php get_search_form(); ?>
        </div>

        <div class="flex flex-col sm:flex-row justify-center gap-3">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--solid"><?php esc_html_e( 'بازگشت به صفحه اصلی', 'negarin' ); ?></a>
            <?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn--outline"><?php esc_html_e( 'مشاهده فروشگاه', 'negarin' ); ?></a>
            <?php endif; ?>
        </div>
    </main>
<?php
get_footer();