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
		$output .= '<ul class="divide-y divide-black/10">';
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
				$is_account ? '<span class="dashicons dashicons-admin-users"></span>' : '<span></span>'
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
