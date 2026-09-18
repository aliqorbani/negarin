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
        add_theme_support('woocommerce', [
            'thumbnail_image_width' => 84,
            'single_image_width'    => 600,
        ]);
		add_image_size( 'negarin-hero', 1440, 600, true );
		add_image_size( 'negarin-section-half', 600, 0, false );
		add_image_size( 'negarin-grid-2', 600, 0, false );
		add_image_size( 'negarin-grid-3', 390, 0, false );
		add_image_size( 'negarin-product-card', 600, 1400, true );
	}
);
