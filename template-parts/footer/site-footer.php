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

$message_status = isset( $_GET['negarin_msg'] ) ? sanitize_key( wp_unslash( $_GET['negarin_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<footer class="negarin-footer bg-white mt-16 pt-16 pb-10 text-center">
	<div class="max-w-lg mx-auto px-4">

		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-block mb-8">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="font-serif tracking-[0.35em] text-2xl"><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
		</a>

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="mb-8" aria-label="<?php esc_attr_e( 'لینک‌های فوتر', 'negarin' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'space-y-3 text-sm opacity-80',
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="mb-8">
			<input type="hidden" name="action" value="negarin_footer_message">
			<?php wp_nonce_field( 'negarin_footer_message' ); ?>
			<input type="text" name="website" value="" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

			<p class="text-sm mb-4">
				<?php esc_html_e( 'برای نگارین بنویسید ، با اشتیاق خونده میشه :)', 'negarin' ); ?>
			</p>

			<?php if ( 'sent' === $message_status ) : ?>
				<p class="text-sm text-emerald-600 mb-3"><?php esc_html_e( 'پیام شما ارسال شد، ممنون از شما :)', 'negarin' ); ?></p>
			<?php elseif ( 'failed' === $message_status ) : ?>
				<p class="text-sm text-red-600 mb-3"><?php esc_html_e( 'ارسال پیام با خطا مواجه شد.', 'negarin' ); ?></p>
			<?php elseif ( 'empty' === $message_status ) : ?>
				<p class="text-sm text-red-600 mb-3"><?php esc_html_e( 'لطفاً پیام خود را بنویسید.', 'negarin' ); ?></p>
			<?php endif; ?>

			<div class="flex flex-col sm:flex-row items-stretch gap-2">
				<input
					type="text"
					name="message"
					placeholder="<?php esc_attr_e( 'اینجا بنویسید...', 'negarin' ); ?>"
					class="flex-1 border border-black/15 rounded-sm px-4 py-3 text-sm order-2 sm:order-1"
				>
				<button type="submit" class="btn btn--solid order-1 sm:order-2">
					<?php esc_html_e( 'ارسال', 'negarin' ); ?>
				</button>
			</div>
		</form>

		<?php $socials = negarin_option( 'socials', array() ); ?>
		<?php if ( $socials ) : ?>
			<div class="flex items-center justify-center gap-4 mb-8">
				<?php foreach ( $socials as $social ) : ?>
					<a href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener" class="w-9 h-9 rounded-full border border-black/10 flex items-center justify-center opacity-80 hover:opacity-100" aria-label="<?php echo esc_attr( ucfirst( $social['platform'] ) ); ?>">
						<span class="dashicons dashicons-share"></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
			<div class="mb-8 text-sm opacity-70"><?php dynamic_sidebar( 'footer-1' ); ?></div>
		<?php endif; ?>

		<p class="text-xs opacity-50">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> — <?php esc_html_e( 'تمامی حقوق محفوظ است.', 'negarin' ); ?>
		</p>

	</div>
</footer>
