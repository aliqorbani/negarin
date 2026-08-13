<?php
/**
 * Section: Banner — full-width image only (e.g. packaging shots), optional link.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image = get_sub_field( 'banner_image' );
$link  = get_sub_field( 'banner_link' );
$tag   = ! empty( $link['url'] ) ? 'a' : 'div';
?>
<section class="negarin-banner py-4">
	<<?php echo esc_html( $tag ); ?>
		<?php if ( 'a' === $tag ) : ?>
			href="<?php echo esc_url( $link['url'] ); ?>"
			<?php echo ! empty( $link['target'] ) ? 'target="_blank" rel="noopener"' : ''; ?>
		<?php endif; ?>
		class="block w-full"
	>
		<?php negarin_image( $image, 'negarin-hero', 'w-full h-auto object-cover' ); ?>
	</<?php echo esc_html( $tag ); ?>>
</section>
