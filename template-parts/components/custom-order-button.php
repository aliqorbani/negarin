<?php
/**
 * "سفارش شخصی" trigger button. Include this inside the single-product
 * `x-data="{ customOrderOpen:false, sizeGuideOpen:false }"` wrapper — see
 * woocommerce/content-single-product.php once the single-product design
 * arrives. Renders nothing for products that haven't opted in.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Negarin\Services\CustomOrder;

$product_id = get_the_ID();

if ( ! CustomOrder::is_enabled_for( $product_id ) ) {
	return;
}
?>
<button type="button" class="btn btn--solid w-full mt-3 md:mt-0" @click="customOrderOpen = true">
	<?php esc_html_e( 'سفارش شخصی', 'negarin' ); ?>
</button>

<?php get_template_part( 'template-parts/components/custom-order-modal' ); ?>
