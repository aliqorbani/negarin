<?php
/**
 * "انتخاب سایز" trigger. Include this inside the single-product
 * `x-data="{ sizeSelectOpen:false, sizeChartOpen:false, customOrderOpen:false, ... }"`
 * wrapper (see woocommerce/content-single-product.php). Renders nothing
 * for non-sized products — those keep the plain add-to-cart button.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Negarin\Services\ProductSizing;

global $product;

if ( ! $product instanceof WC_Product_Variable || ! ProductSizing::is_sized_product( $product ) ) {
	return;
}
?>
<button type="button" class="btn btn--solid w-full col-span-2" @click="sizeSelectOpen = true">
	<?php esc_html_e( 'انتخاب سایز', 'negarin' ); ?>
</button>

<?php get_template_part( 'template-parts/components/size-select-modal' ); ?>
