<?php
/**
 * "محصولات مرتبط" — shown on single posts when either manually selected
 * or resolvable from the focus keyword (see inc/services/BlogFields.php).
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Negarin\Services\BlogFields;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$products = BlogFields::get_related_products( get_the_ID() );

if ( empty( $products ) ) {
	return;
}
?>
<section class="mt-16 pt-10 border-t border-black/10">
	<h2 class="font-serif text-xl mb-6 text-right"><?php esc_html_e( 'محصولات مرتبط', 'negarin' ); ?></h2>
	<div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
		<?php
		global $post;
		foreach ( $products as $product ) {
			$post                = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			$GLOBALS['product']  = $product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			setup_postdata( $post );
			wc_get_template_part( 'content', 'product' );
		}
		wp_reset_postdata();
		?>
	</div>
</section>
