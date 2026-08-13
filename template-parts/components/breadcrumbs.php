<?php
/**
 * Renders negarin_get_breadcrumbs() as a simple text trail.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$crumbs = negarin_get_breadcrumbs();

if ( count( $crumbs ) < 2 ) {
	return;
}
?>
<nav class="text-xs opacity-60 mb-6 flex flex-wrap gap-1" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'negarin' ); ?>">
	<?php foreach ( $crumbs as $index => $crumb ) : ?>
		<?php if ( $index > 0 ) : ?>
			<span>‹</span>
		<?php endif; ?>
		<?php if ( ! empty( $crumb['url'] ) && $index < count( $crumbs ) - 1 ) : ?>
			<a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
		<?php else : ?>
			<span><?php echo esc_html( $crumb['label'] ); ?></span>
		<?php endif; ?>
	<?php endforeach; ?>
</nav>
