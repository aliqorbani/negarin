<?php
/**
 * Main site header: logo on the start side, utility icon cluster (account,
 * search, cart, menu) on the end side — matches the Figma export. The
 * hamburger opens a full-screen off-canvas menu (see offcanvas-menu.php)
 * at every breakpoint, there is no separate desktop horizontal nav.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header class="negarin-header sticky top-0 z-40 bg-white border-b border-negarin-line" x-data="{ menuOpen: false, searchOpen: false }">
	<div class="container max-w-7xl mx-auto px-4 py-4 flex flex-row-reverse items-center justify-between">

        <?php if ( has_custom_logo() ) : ?>

            <?php
            $custom_logo_id = get_theme_mod( 'custom_logo' );
            $logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
            ?>

            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="negarin-logo">
                <img
                        src="<?php echo esc_url( $logo_url ); ?>"
                        class="logo-image max-w-[90px] md:max-w-[240px]"
                        alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
                >
            </a>

        <?php else : ?>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="negarin-logo">
            <span class="font-serif tracking-[0.35em] text-xl">
                <?php bloginfo( 'name' ); ?>
            </span>
        </a>

        <?php endif; ?>

		<div class="flex items-center gap-4 text-lg flex-row-reverse">
			<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '#' ); ?>" aria-label="<?php esc_attr_e( 'حساب کاربری', 'negarin' ); ?>">
                <svg width="17" height="19" viewBox="0 0 17 19" fill="none">
                    <path d="M16.5 18.5C16.5 17.1044 16.5 16.4067 16.3278 15.8389C15.94 14.5605 14.9395 13.56 13.6611 13.1722C13.0933 13 12.3956 13 11 13H6C4.60444 13 3.90665 13 3.33886 13.1722C2.06045 13.56 1.06004 14.5605 0.67224 15.8389C0.5 16.4067 0.5 17.1044 0.5 18.5M13 5C13 7.48528 10.9853 9.5 8.5 9.5C6.01472 9.5 4 7.48528 4 5C4 2.51472 6.01472 0.5 8.5 0.5C10.9853 0.5 13 2.51472 13 5Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
			<button @click="searchOpen = !searchOpen" aria-label="<?php esc_attr_e( 'جستجو', 'negarin' ); ?>">
                <svg width="19" height="19" viewBox="0 0 19 19" fill="none">
                    <path d="M18.5 18.5L14.15 14.15M16.5 8.5C16.5 12.9183 12.9183 16.5 8.5 16.5C4.08172 16.5 0.5 12.9183 0.5 8.5C0.5 4.08172 4.08172 0.5 8.5 0.5C12.9183 0.5 16.5 4.08172 16.5 8.5Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
			<a href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#' ); ?>" class="relative" aria-label="<?php esc_attr_e( 'سبد خرید', 'negarin' ); ?>">
                <svg width="20" height="19" viewBox="0 0 20 19" fill="none">
                    <path d="M13.6978 5.5C13.6978 6.56087 13.2764 7.57828 12.5263 8.32843C11.7761 9.07857 10.7587 9.5 9.69783 9.5C8.63696 9.5 7.61955 9.07857 6.8694 8.32843C6.11926 7.57828 5.69783 6.56087 5.69783 5.5M1.33105 4.90138L0.631049 13.3014C0.480672 15.1059 0.405483 16.0082 0.710515 16.7042C0.978516 17.3157 1.44286 17.8204 2.03002 18.1382C2.6983 18.5 3.60369 18.5 5.41447 18.5H13.9812C15.792 18.5 16.6974 18.5 17.3656 18.1382C17.9528 17.8204 18.4171 17.3157 18.6851 16.7042C18.9902 16.0082 18.915 15.1059 18.7646 13.3014L18.0646 4.90138C17.9352 3.34875 17.8705 2.57243 17.5267 1.98486C17.2239 1.46744 16.7731 1.0526 16.2323 0.793846C15.6182 0.500001 14.8392 0.500001 13.2812 0.500001L6.11447 0.5C4.55645 0.5 3.77745 0.5 3.16335 0.793844C2.62257 1.0526 2.17173 1.46744 1.86896 1.98486C1.52513 2.57243 1.46043 3.34875 1.33105 4.90138Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                	<?php $cart_count = WC()->cart->get_cart_contents_count();
                    if($cart_count > 0){
                        echo '<span class="negarin-cart-count absolute -top-2 -left-2 text-[10px] bg-negarin-ink text-white rounded-full w-4 h-4 flex items-center justify-center">';
                        get_template_part( 'template-parts/components/cart-drawer-count' );
                        echo '</span>';
                    } ?>
			</a>
			<button @click="menuOpen = true" aria-label="<?php esc_attr_e( 'باز کردن منو', 'negarin' ); ?>">
                <svg width="19" height="13" viewBox="0 0 19 13" fill="none">
                    <path d="M0.5 6.5H18.5M0.5 0.5H18.5M0.5 12.5H18.5" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
		</div>
	</div>

	<div x-show="searchOpen" x-cloak class="border-t border-black/5 px-4 py-3">
		<?php get_search_form(); ?>
	</div>

	<?php get_template_part( 'template-parts/header/offcanvas-menu' ); ?>
</header>
