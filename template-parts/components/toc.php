<?php
/**
 * "فهرست مطالب" box. Expected $args: [ 'items' => array from negarin_extract_toc() ].
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = $args['items'] ?? array();

if ( count( $items ) < 2 ) {
	return;
}
?>
<div class="negarin-toc bg-negarin-cream p-5 mb-8" x-data="{ open: true }">
	<button type="button" class="w-full flex items-center justify-between text-sm font-medium" @click="open = !open">
		<span x-text="open ? '−' : '+'"></span>
		<span><?php esc_html_e( 'فهرست مطالب', 'negarin' ); ?></span>
	</button>
	<ul x-show="open" x-transition class="mt-4 space-y-2 text-sm">
		<?php foreach ( $items as $item ) : ?>
			<li class="<?php echo 3 === $item['level'] ? 'pr-4 opacity-80' : ''; ?>">
				<a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
