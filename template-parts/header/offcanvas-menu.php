<?php
/**
 * Full-screen off-canvas menu, opened via `menuOpen` (declared in
 * site-header.php). `panel` tracks which drill-down level is showing:
 * 0 = root list, otherwise the menu-item ID whose children are visible.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div
	x-show="menuOpen"
	x-cloak
	x-data="{ panel: 0 }"
	class="fixed inset-0 z-50 bg-white overflow-hidden"
	@keydown.escape.window="menuOpen = false"
>
	<div class="relative h-full">

		<div class="flex items-center px-4 py-5 border-b border-black/10">
			<button type="button" class="text-2xl leading-none" @click="menuOpen = false" aria-label="<?php esc_attr_e( 'بستن منو', 'negarin' ); ?>">&times;</button>
			<span class="flex-1 text-center font-serif tracking-[0.3em] text-lg"><?php bloginfo( 'name' ); ?></span>
		</div>

		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '<ul class="divide-y divide-black/10">%3$s</ul>',
				'walker'         => new \Negarin\Classes\OffcanvasMenuWalker(),
				'fallback_cb'    => false,
			)
		);
		?>
	</div>
</div>
