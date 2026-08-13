<?php
/**
 * Nav menu fallback + active-link class helper.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'nav_menu_css_class',
	function ( $classes, $item ) {
		if ( in_array( 'current-menu-item', $classes, true ) ) {
			$classes[] = 'is-active';
		}
		return $classes;
	},
	10,
	2
);
