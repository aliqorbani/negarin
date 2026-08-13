<?php
/**
 * WooCommerce My Account wrapper override.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Negarin\Services\AccountMenu;

if ( ! is_user_logged_in() ) {
	get_template_part( 'template-parts/components/otp-login-form' );
	return;
}

$current_user = wp_get_current_user();
$phone         = get_user_meta( $current_user->ID, 'negarin_phone', true ) ?: get_user_meta( $current_user->ID, 'billing_phone', true );
$order_count   = AccountMenu::order_count( $current_user->ID );

do_action( 'woocommerce_before_account_navigation' );
?>

<div class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-[1fr_320px] gap-8 items-start">

	<div class="order-2 md:order-1">
		<?php do_action( 'woocommerce_account_content' ); ?>
	</div>

	<div class="order-1 md:order-2 bg-negarin-cream p-6 text-right">
		<p class="font-serif text-lg mb-1">
			<?php
			printf(
				/* translators: %s: customer first name */
				esc_html__( 'سلام %s!', 'negarin' ),
				esc_html( $current_user->first_name ?: $current_user->display_name )
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
						<?php if ( 'orders' === $endpoint && $order_count > 0 ) : ?>
							<span class="bg-red-500 text-white text-[10px] rounded-full w-5 h-5 flex items-center justify-center"><?php echo esc_html( $order_count ); ?></span>
						<?php endif; ?>
					</span>
					<span><?php echo esc_html( $label ); ?></span>
				</a>
			<?php endforeach; ?>
		</nav>
	</div>

</div>
