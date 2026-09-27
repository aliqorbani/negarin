<?php
/**
 * Footer: centered logo, quick-question nav links, a short "write to us"
 * message form, and social icons — matches the export (same centered
 * layout at every breakpoint, not just mobile).
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<footer class="negarin-footer border-t bg-white mt-16 pt-16 pb-10 text-center relative">
    <div class="max-w-lg mx-auto px-4">

        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-block mb-8">
            <?php if ( has_custom_logo() ) : ?>

                <?php
                $custom_logo_id = get_theme_mod( 'custom_logo' );
                $logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
                ?>

                <img
                        src="<?php echo esc_url( $logo_url ); ?>"
                        class="logo-image"
                        alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
                >

            <?php else : ?>
                <span class="font-serif tracking-[0.35em] text-2xl"><?php bloginfo( 'name' ); ?></span>
            <?php endif; ?>
        </a>

        <?php
        $show_site_footer = false;
        if(function_exists('is_woocommerce') && ! is_woocommerce() && ! is_front_page()){
            $show_site_footer = true;
        }
        if( $show_site_footer ) :
        if ( has_nav_menu( 'footer' ) ) : ?>
            <nav class="mb-6" aria-label="<?php esc_attr_e( 'لینک‌های فوتر', 'negarin' ); ?>">
                <?php
                wp_nav_menu(
                        array(
                                'theme_location' => 'footer',
                                'container'      => false,
                                'menu_class'     => 'space-y-6 text-sm md:text-lg',
                                'fallback_cb'    => false,
                        )
                );
                ?>
            </nav>
        <?php endif; ?>

        <form @submit.prevent="submit()" x-data="negarinFooterMessage()" class="mb-8">
            <input type="text" x-model="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

            <p class="text-sm md:text-lg mb-2 md:mb-4">
                <?php esc_html_e( 'برای نگارین بنویسید ، با اشتیاق خونده میشه :)', 'negarin' ); ?>
            </p>

            <p class="text-sm text-emerald-600 mb-3" x-show="status === 'sent'"><?php esc_html_e( 'پیام شما ارسال شد، ممنون از شما :)', 'negarin' ); ?></p>
            <p class="text-sm text-red-600 mb-3" x-show="status === 'empty'"><?php esc_html_e( 'لطفاً پیام خود را بنویسید.', 'negarin' ); ?></p>

            <div class="flex sm:flex-row items-stretch gap-3">
				<textarea title="اینجا بنویسید..."
                          x-model="message"
                          rows="1"
                          placeholder="<?php esc_attr_e( 'اینجا بنویسید...', 'negarin' ); ?>"
                          class="flex-1 border border-negarin-gray rounded-sm px-4 py-3 text-sm"
                ></textarea>
                <button type="submit" class="btn btn--solid" :disabled="submitting">
                    <span x-show="!submitting"><?php esc_html_e( 'ارسال', 'negarin' ); ?></span>
                    <span x-show="submitting"><?php esc_html_e( 'در حال ارسال...', 'negarin' ); ?></span>
                </button>
            </div>
        </form>

        <?php
        endif;
        $socials = negarin_option( 'socials', array() ); ?>
        <?php if ( $socials ) : ?>
            <div class="flex items-center justify-center gap-4 mb-8">
                <?php foreach ( $socials as $social ) : ?>
                    <a href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener" class="w-9 h-9 rounded-full border border-black/10 flex items-center justify-center opacity-80 hover:opacity-100" aria-label="<?php echo esc_attr( ucfirst( $social['platform'] ) ); ?>">
                        <?php echo negarin_social_icon($social['platform']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
            <div class="mb-8 text-sm opacity-70"><?php dynamic_sidebar( 'footer-1' ); ?></div>
        <?php endif; ?>

        <p class="text-xs opacity-0">
            &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> — <?php esc_html_e( 'تمامی حقوق محفوظ است.', 'negarin' ); ?>
        </p>
        <a style="position:absolute; right:0; bottom:0" referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=7620962&Code=F87Th2LK8J7XmMhIT1DESMn2J5jX998Z'><img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=7620962&Code=F87Th2LK8J7XmMhIT1DESMn2J5jX998Z' alt='' style='cursor:pointer' code='F87Th2LK8J7XmMhIT1DESMn2J5jX998Z'></a>
    </div>
</footer>