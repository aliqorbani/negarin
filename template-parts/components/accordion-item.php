<?php
/**
 * A single accordion row (+/- toggle). Reused for product specs / care /
 * and anywhere else a collapsible block is needed (FAQ, etc).
 *
 * Expected $args: [ 'title' => string, 'content' => string (HTML, already kses'd) ]
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title   = $args['title'] ?? '';
$content = $args['content'] ?? '';

if ( ! $title || ! $content ) {
	return;
}
?>
<div class="negarin-accordion border-t border-black/10" x-data="{ open: false }">
	<button type="button" class="w-full flex items-center justify-between py-4 text-sm" @click="open = !open">
		<span class="text-lg leading-none" x-text="open ? '−' : '+'"></span>
		<span><?php echo esc_html( $title ); ?></span>
	</button>
	<div x-show="open" x-transition class="pb-4 text-sm opacity-75 leading-8">
		<?php echo wp_kses_post( $content ); ?>
	</div>
</div>
