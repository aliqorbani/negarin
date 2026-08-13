<?php
/**
 * WooCommerce "My Orders" override.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! wc_get_page_id( 'myaccount' ) ) {
	return;
}

$customer_orders = wc_get_orders(
	array(
		'customer' => get_current_user_id(),
		'page'     => $wp->query_vars['orders'] ?? 1,
		'paginate' => true,
	)
);

/**
 * Status label + badge color — matches the export's green
 * "تحویل داده شده" / amber "در حال آماده سازی" pills.
 */
$status_styles = array(
	'completed'  => array( 'label' => __( 'تحویل داده شده', 'negarin' ), 'class' => 'bg-emerald-50 text-emerald-600' ),
	'processing' => array( 'label' => __( 'در حال آماده سازی', 'negarin' ), 'class' => 'bg-amber-50 text-amber-600' ),
	'on-hold'    => array( 'label' => __( 'در انتظار بررسی', 'negarin' ), 'class' => 'bg-amber-50 text-amber-600' ),
	'pending'    => array( 'label' => __( 'در انتظار پرداخت', 'negarin' ), 'class' => 'bg-gray-100 text-gray-600' ),
	'cancelled'  => array( 'label' => __( 'لغو شده', 'negarin' ), 'class' => 'bg-red-50 text-red-600' ),
	'refunded'   => array( 'label' => __( 'بازگشت وجه', 'negarin' ), 'class' => 'bg-gray-100 text-gray-600' ),
	'failed'     => array( 'label' => __( 'ناموفق', 'negarin' ), 'class' => 'bg-red-50 text-red-600' ),
);
?>

<?php if ( ! $customer_orders->orders ) : ?>

	<div class="py-16 text-center opacity-60">
		<?php esc_html_e( 'هنوز سفارشی ثبت نکرده‌اید.', 'negarin' ); ?>
	</div>

<?php else : ?>

	<table class="w-full text-sm text-right">
		<thead>
			<tr class="bg-negarin-cream text-xs">
				<th class="py-3 px-3 font-normal"><?php esc_html_e( 'نام محصول', 'negarin' ); ?></th>
				<th class="py-3 px-3 font-normal"><?php esc_html_e( 'تاریخ سفارش', 'negarin' ); ?></th>
				<th class="py-3 px-3 font-normal"><?php esc_html_e( 'تعداد', 'negarin' ); ?></th>
				<th class="py-3 px-3 font-normal"><?php esc_html_e( 'وضعیت سفارش', 'negarin' ); ?></th>
				<th class="py-3 px-3 font-normal"></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $customer_orders->orders as $order ) : ?>
				<?php
				$items      = $order->get_items();
				$first_item = reset( $items );
				$item_count = array_sum( wp_list_pluck( $items, 'quantity' ) );
				$status     = $status_styles[ $order->get_status() ] ?? array(
					'label' => wc_get_order_status_name( $order->get_status() ),
					'class' => 'bg-gray-100 text-gray-600',
				);
				?>
				<tr class="border-b border-black/5">
					<td class="py-4 px-3"><?php echo $first_item ? esc_html( $first_item->get_name() ) : esc_html__( 'سفارش', 'negarin' ); ?></td>
					<td class="py-4 px-3" dir="ltr"><?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'Y/m/d' ) ); ?></td>
					<td class="py-4 px-3"><?php printf( esc_html__( '%d عدد', 'negarin' ), (int) $item_count ); ?></td>
					<td class="py-4 px-3">
						<span class="inline-block rounded-full px-3 py-1 text-xs <?php echo esc_attr( $status['class'] ); ?>">
							<?php echo esc_html( $status['label'] ); ?>
						</span>
					</td>
					<td class="py-4 px-3">
						<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="flex items-center gap-1 text-xs">
							<span>‹</span>
							<span><?php esc_html_e( 'مشاهده جزئیات', 'negarin' ); ?></span>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( $customer_orders->max_num_pages > 1 ) : ?>
		<div class="mt-6 flex justify-center gap-2 text-sm">
			<?php
			echo paginate_links( // phpcs:ignore
				array(
					'base'      => esc_url_raw( wc_get_endpoint_url( 'orders', '%#%' ) ),
					'format'    => '%#%',
					'current'   => $wp->query_vars['orders'] ?? 1,
					'total'     => $customer_orders->max_num_pages,
					'prev_text' => '‹',
					'next_text' => '›',
				)
			);
			?>
		</div>
	<?php endif; ?>

<?php endif; ?>
