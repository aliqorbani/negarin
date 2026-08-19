<?php
/**
 * Off-canvas menu: a dimmed backdrop plus a drawer anchored to the right
 * edge (full width on mobile, capped from `sm` up so it never goes
 * full-screen on desktop). Opened via `menuOpen` (declared in
 * site-header.php); both backdrop and drawer slide/fade in with a
 * transition. Closes on the X button, the backdrop, Escape, or clicking
 * any leaf link. `panel` tracks which drill-down level is showing:
 * 0 = root list, otherwise the menu-item ID whose children are visible.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div x-data="{ panel: 0 }" @keydown.escape.window="menuOpen = false; panel = 0">
    <!-- Backdrop -->
    <div
            x-show="menuOpen"
            x-cloak
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-black/75"
            @click="menuOpen = false; panel = 0"
    ></div>

    <!-- Drawer -->
    <div
            x-show="menuOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition linear duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 z-50 h-full w-full sm:max-w-sm md:max-w-md bg-white overflow-hidden shadow-xl <?php echo is_admin_bar_showing() ? 'translate-y-8': '' ?>"
    >
        <div class="relative h-full">
            <div class="relative flex items-center justify-center px-4 py-5 border-b border-black/10">
                <button type="button" class="absolute left-4 text-2xl leading-none" @click="menuOpen = false; panel = 0" aria-label="<?php esc_attr_e( 'بستن منو', 'negarin' ); ?>">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M14.1667 5.83331L5.83337 14.1666M5.83337 5.83331L14.1667 14.1666" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <?php if ( has_custom_logo() ) :
                    $custom_logo_id = get_theme_mod( 'custom_logo' );
                    $logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
                    ?><img src="<?php echo esc_url( $logo_url ); ?>" class="logo-image h-[30px]" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                <?php else : ?>
                <span class="font-serif tracking-[0.3em] text-lg"><?php bloginfo( 'name' ); ?></span>
                <?php endif;?>
            </div>

            <?php
            wp_nav_menu(
                    array(
                            'theme_location' => 'primary',
                            'container'      => false,
                            'items_wrap'     => '<ul class="main-menu-list">%3$s</ul>',
                            'walker'         => new \Negarin\Classes\OffcanvasMenuWalker(),
                            'fallback_cb'    => false,
                    )
            );
            ?>
        </div>
    </div>
</div>
