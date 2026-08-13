<?php
/**
 * Slim announcement bar above the header, toggleable + editable in Theme Options.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! negarin_option( 'announcement_enabled' ) ) {
	return;
}

$text = negarin_option( 'announcement_text' );
$link = negarin_option( 'announcement_link' );

if ( ! $text ) {
	return;
}
?>
<div class="negarin-announcement bg-negarin-ink text-white text-center text-xs md:text-sm py-2">
	<?php if ( ! empty( $link['url'] ) ) : ?>
		<a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $text ); ?></a>
	<?php else : ?>
		<span><?php echo esc_html( $text ); ?></span>
	<?php endif; ?>
</div>
