<?php
/**
 * Fallback template (blog listing / catch-all). Full blog templates
 * (archive.php, single.php, search.php, 404.php) land in the next phase
 * alongside the Blog page design.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main-content" class="max-w-4xl mx-auto px-4 py-16">
	<?php if ( have_posts() ) : ?>
		<div class="grid gap-10">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'border-b border-black/10 pb-8' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'negarin-grid-2', array( 'class' => 'w-full h-auto mb-4' ) ); ?></a>
					<?php endif; ?>
					<h2 class="font-serif text-2xl mb-2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<div class="opacity-70 text-sm leading-7"><?php the_excerpt(); ?></div>
				</article>
				<?php
			endwhile;
			?>
		</div>
		<div class="mt-10"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<p><?php esc_html_e( 'محتوایی یافت نشد.', 'negarin' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
