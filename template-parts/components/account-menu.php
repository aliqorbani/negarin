<?php
/**
 * Greeting + phone + My Account nav links.
 *
 * Two consumers of this exact markup, styled responsively in one file
 * rather than duplicated:
 *  - Desktop: the sticky bordered sidebar, shown on every account endpoint
 *    (see woocommerce/myaccount/my-account.php).
 *  - Mobile: the full-page "menu" shown only on the dashboard root; inner
 *    endpoints instead surface a slim sticky link back here from
 *    template-parts/header/site-header.php.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Negarin\Services\AccountMenu;

if ( ! is_user_logged_in() ) {
    return;
}

$current_user = wp_get_current_user();
$phone        = get_user_meta( $current_user->ID, 'negarin_phone', true ) ?: get_user_meta( $current_user->ID, 'billing_phone', true );
$order_count  = AccountMenu::order_count( $current_user->ID );

// Same fallback as form-edit-account.php: covers customers who ordered
// before ever opening their profile (see the sync hooks in
// inc/hooks/woocommerce.php for how these two stay matched going forward).
$display_name = $current_user->first_name
        ?: get_user_meta( $current_user->ID, 'billing_first_name', true )
                ?: $current_user->display_name;

// Figma (145:492 desktop / 149:692 mobile): rows have no border of their
// own on desktop, and on mobile every row except the last ("خروج") gets a
// bottom border — so we need to know which endpoint is last to skip it.
$menu_items    = wc_get_account_menu_items();
$last_endpoint = array_key_last( $menu_items );
?>
<div class="md:bg-white md:border md:border-[#e4e7ec] md:p-6 text-right">
    <p class="font-serif text-lg mb-1">
        <?php
        printf(
        /* translators: %s: customer first name */
                esc_html__( 'سلام %s!', 'negarin' ),
                esc_html( $display_name )
        );
        ?>
    </p>
    <?php if ( $phone ) : ?>
        <p class="text-sm opacity-60 mb-4" dir="ltr"><?php echo esc_html( $phone ); ?></p>
    <?php endif; ?>

    <nav class="border-t border-[#e4e7ec] pt-3 flex flex-col gap-3">
        <?php foreach ( $menu_items as $endpoint => $label ) :
            $is_active = is_account_page() && is_wc_endpoint_url( $endpoint );
            $is_last   = $endpoint === $last_endpoint;

            $row_classes   = array( 'flex', 'items-center', 'justify-between', 'h-[54px]', 'md:h-12', 'text-sm' );
            $row_classes[] = $is_active ? 'md:bg-[#f0f1f2] font-medium' : 'opacity-70';
            if ( ! $is_last ) {
                $row_classes[] = 'max-md:border-b max-md:border-[#e4e7ec]';
            }
            ?>
            <a
                    href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"
                    class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>"
            >
				<span class="flex items-center gap-2">
					<span class="dashicons <?php echo esc_attr( AccountMenu::dashicon_for( $endpoint ) ); ?>"></span>
					<span><?php echo esc_html( $label ); ?></span>
					<?php if ( 'orders' === $endpoint && $order_count > 0 ) : ?>
                        <span class="bg-[#ff383c] text-white text-xs leading-none rounded-full h-4 min-w-[24px] px-1 flex items-center justify-center"><?php echo esc_html( $order_count ); ?></span>
                    <?php endif; ?>
				</span>
                <svg class="md:hidden shrink-0" width="7" height="12" viewBox="0 0 7 12" fill="none" aria-hidden="true">
                    <path d="M1 1L6 6L1 11" stroke="#333333" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        <?php endforeach; ?>
    </nav>
</div>