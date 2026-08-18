<?php
/**
 * Renders a WordPress nav menu as a drill-down off-canvas panel: top-level
 * items with children become a "‹ /  ›" sliding sub-panel instead of an
 * inline dropdown — matching the Figma export. Fully driven by whatever
 * menu is assigned to the `primary` location in Appearance -> Menus;
 * nothing here is hardcoded.
 *
 * @package Negarin
 */

namespace Negarin\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OffcanvasMenuWalker extends \Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		// Sub-panel wrapper: hidden until its parent trigger is active.
		$output .= '<div x-show="panel === ' . (int) ( $this->current_parent_id ?? 0 ) . '" x-cloak class="absolute inset-0 bg-white">';
		$output .= '<div class="flex items-center px-4 py-5 border-b border-black/10">';
		$output .= '<button type="button" class="text-xl leading-none" @click="panel = 0" aria-label="' . esc_attr__( 'بازگشت', 'negarin' ) . '">‹</button>';
		$output .= '<span class="flex-1 text-center font-serif tracking-[0.3em]">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
		$output .= '</div>';
		$output .= '<ul>';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul></div>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$has_children = in_array( $item->ID, $this->get_parent_ids(), true );

		if ( 0 === $depth ) {
			$this->current_parent_id = $item->ID;
		}

		$output .= '<li class="border-b border-black/10">';

		if ( $has_children ) {
			$output .= sprintf(
				'<button type="button" class="w-full flex items-center justify-between px-4 py-5 text-sm" @click="panel = %1$d">
					<span>‹</span>
					<span>%2$s</span>
				</button>',
				(int) $item->ID,
				esc_html( $item->title )
			);
		} else {
			$is_account = function_exists( 'wc_get_page_permalink' ) && untrailingslashit( $item->url ) === untrailingslashit( wc_get_page_permalink( 'myaccount' ) );
			$output    .= sprintf(
				'<a href="%1$s" class="flex items-center justify-between px-4 py-5 text-sm">%3$s<span>%2$s</span></a>',
				esc_url( $item->url ),
				esc_html( $item->title ),
				esc_html('<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.99998 10C5.8866 10 4.00718 11.0204 2.81064 12.604C2.55312 12.9448 2.42436 13.1152 2.42857 13.3455C2.43182 13.5235 2.54356 13.7479 2.68356 13.8578C2.86477 14 3.11589 14 3.61814 14H12.3818C12.8841 14 13.1352 14 13.3164 13.8578C13.4564 13.7479 13.5681 13.5235 13.5714 13.3455C13.5756 13.1152 13.4468 12.9448 13.1893 12.604C11.9928 11.0204 10.1134 10 7.99998 10Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/><path d="M7.99998 8C9.65684 8 11 6.65685 11 5C11 3.34315 9.65684 2 7.99998 2C6.34313 2 4.99998 3.34315 4.99998 5C4.99998 6.65685 6.34313 8 7.99998 8Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/></svg>')
			);
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}

	/**
	 * IDs of any menu item that is a parent (has at least one child) —
	 * computed once from the full menu tree passed to walk().
	 */
	private ?array $parent_ids_cache = null;
	private $current_parent_id      = 0;

	public function walk( $elements, $max_depth, ...$args ) {
		$this->parent_ids_cache = array();
		foreach ( $elements as $element ) {
			if ( $element->menu_item_parent ) {
				$this->parent_ids_cache[] = (int) $element->menu_item_parent;
			}
		}
		return parent::walk( $elements, $max_depth, ...$args );
	}

	private function get_parent_ids(): array {
		return $this->parent_ids_cache ?? array();
	}
}
