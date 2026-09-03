<?php
/**
 * WooCommerce My Account wrapper override.
 *
 * Layout differs by breakpoint rather than by endpoint:
 *  - Desktop: content + the account-menu sidebar are both always visible,
 *    sidebar sticky below the site header (offset set in assets/js/header-offset.js).
 *  - Mobile: only one of the two shows at a time. On the dashboard root
 *    (no endpoint) the account-menu partial IS the page — it's the "menu"
 *    screen from the Figma export. On any other endpoint (edit-account,
 *    orders, ...) the endpoint's own content shows instead, and the way
 *    back to the menu is the slim sticky row site-header.php adds to the
 *    header on those pages.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_user_logged_in() ) {
    get_template_part( 'template-parts/components/otp-login-form' );
    return;
}

do_action( 'woocommerce_before_account_navigation' );

$is_dashboard_root = ! is_wc_endpoint_url();
?>

<div class="container max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-[320px_1fr] gap-8 items-start">

    <?php
    /*
     * `negarin-account-content` is load-bearing, not just a styling hook:
     * assets/js/spa.js targets `.negarin-account-content form` to opt every
     * My Account form (edit-account, edit-address, lost-password, ...) out
     * of Turbo Drive's form handling. WooCommerce's own account form
     * handlers only redirect on success — on a validation error they just
     * re-render the same page (200, no redirect), which Turbo Drive treats
     * as a hard error for any form it intercepted ("Form responses must
     * redirect to another location"). Keep this class on whatever wraps
     * `woocommerce_account_content` even if the surrounding markup changes.
     */
    ?>
    <div class="negarin-account-content <?php echo $is_dashboard_root ? 'hidden md:block' : ''; ?> order-2 md:col-start-2">
        <?php do_action( 'woocommerce_account_content' ); ?>
    </div>

    <div class="<?php echo $is_dashboard_root ? '' : 'hidden md:block'; ?> order-1 md:col-start-1 md:sticky md:top-[calc(var(--negarin-header-h,80px)+1.5rem)]">
        <?php get_template_part( 'template-parts/components/account-menu' ); ?>
    </div>

</div>