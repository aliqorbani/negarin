<?php
/**
 * Default page template (used when a Page does NOT use the "Page Builder" template).
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main-content" class="max-w-3xl mx-auto px-4 py-16">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class(); ?>>
			<h1 class="font-serif text-3xl mb-6"><?php the_title(); ?></h1>
			<div class="prose max-w-none leading-8">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
