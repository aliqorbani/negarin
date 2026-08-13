<?php
/**
 * Custom image sizes used by homepage sections & product cards.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function () {
		add_image_size( 'negarin-hero', 1600, 900, true );
		add_image_size( 'negarin-section-half', 900, 1100, true );
		add_image_size( 'negarin-grid-2', 760, 950, true );
		add_image_size( 'negarin-grid-3', 520, 650, true );
		add_image_size( 'negarin-product-card', 600, 750, true );
	}
);
