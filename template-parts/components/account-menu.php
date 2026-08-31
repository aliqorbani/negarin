<?php
/**
 * Greeting + phone + My Account nav links.
 *
 * Two consumers of this exact markup, styled responsively in one file
 * rather than duplicated:
 *  - Desktop: the sticky cream sidebar, shown on every account endpoint
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
?>
<div class="md:bg-negarin-cream md:p-6 text-right">
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

    <nav class="border-t border-black/10">
        <?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
            <a
                href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"
                class="flex items-center justify-between py-4 border-b border-black/10 text-sm <?php echo is_account_page() && is_wc_endpoint_url( $endpoint ) ? 'font-medium' : 'opacity-70'; ?>"
            >
				<span class="flex items-center gap-2">
					<span class="dashicons <?php echo esc_attr( AccountMenu::dashicon_for( $endpoint ) ); ?>"></span>
					<span><?php echo esc_html( $label ); ?></span>
					<?php if ( 'orders' === $endpoint && $order_count > 0 ) : ?>
                        <span class="bg-red-500 text-white text-[10px] rounded-full w-5 h-5 flex items-center justify-center"><?php echo esc_html( $order_count ); ?></span>
                    <?php endif; ?>
				</span>
                <svg class="md:hidden shrink-0" width="7" height="12" viewBox="0 0 7 12" fill="none" aria-hidden="true">
                    <path d="M1 1L6 6L1 11" stroke="#333333" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        <?php endforeach; ?>
    </nav>
</div>