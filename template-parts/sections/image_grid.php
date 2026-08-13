<?php
/**
 * Section: Image Grid — 2 or 3 images side by side, each with optional link/caption.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$columns   = get_sub_field( 'columns' ) ?: '2';
$col_class = '3' === $columns ? 'grid-cols-2 md:grid-cols-3' : 'grid-cols-2';
$size      = '3' === $columns ? 'negarin-grid-3' : 'negarin-grid-2';
?>
<section class="negarin-image-grid py-4">
	<div class="grid gap-2 md:gap-3 max-w-7xl mx-auto  <?php echo esc_attr( $col_class ); ?>">
		<?php if ( have_rows( 'items' ) ) : ?>
			<?php while ( have_rows( 'items' ) ) : the_row(); ?>
				<?php
				$image   = get_sub_field( 'image' );
				$caption = get_sub_field( 'caption' );
				$link    = get_sub_field( 'link' );
				$tag     = ! empty( $link['url'] ) ? 'a' : 'div';
				?>
				<<?php echo esc_html( $tag ); ?>
					<?php if ( 'a' === $tag ) : ?>
						href="<?php echo esc_url( $link['url'] ); ?>"
						<?php echo ! empty( $link['target'] ) ? 'target="_blank" rel="noopener"' : ''; ?>
					<?php endif; ?>
					class="relative block group overflow-hidden"
				>
					<?php negarin_image( $image, $size, 'w-full h-full object-cover transition-transform duration-500 group-hover:scale-105' ); ?>
					<?php if ( $caption ) : ?>
						<span class="absolute bottom-3 right-3 text-white text-sm bg-black/40 px-2 py-1"><?php echo esc_html( $caption ); ?></span>
					<?php endif; ?>
				</<?php echo esc_html( $tag ); ?>>
			<?php endwhile; ?>
		<?php endif; ?>
	</div>
</section>
