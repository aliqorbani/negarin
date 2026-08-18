<?php
/**
 * Template Name: Page Builder (Flexible Sections)
 *
 * Assign this template to any Page (including the WordPress "static front
 * page") to unlock the section builder: Hero, Image+Text, Image Grid,
 * Banner, Product Carousel — reorderable and independently configurable
 * from wp-admin, no code changes required per page.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main-content" class="flex flex-col gap-y-[60px] md:gap-y-[120px]">
	<?php
	if ( have_rows( 'sections' ) ) :
//        file_put_contents(__DIR__.'/sections.log',serialize(get_field('sections')), LOCK_EX);
		while ( have_rows( 'sections' ) ) :
			the_row();
			$layout = get_row_layout();

			$part   = 'template-parts/sections/' . $layout;

			if ( locate_template( $part . '.php' ) ) {
				get_template_part( $part );
			}
		endwhile;
	else :
		// Fallback so an empty builder page never looks broken to an editor.
		the_content();
	endif;
	?>
</main>

<?php
get_footer();
