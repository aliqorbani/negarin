<?php
/**
 * Blog archive — covers the default posts index, category, tag, author,
 * and date archives (WordPress falls back to this file for all of them
 * unless a more specific template like category.php exists, so category
 * and date archives both work automatically without extra files).
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="max-w-5xl mx-auto px-4 py-10">

	<?php get_template_part( 'template-parts/components/breadcrumbs' ); ?>

	<header class="text-center mb-12">
		<h1 class="font-serif text-3xl mb-2"><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<div class="opacity-70 text-sm max-w-2xl mx-auto">', '</div>' ); ?>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="grid grid-cols-1 md:grid-cols-2 gap-10">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'text-right' ); ?>>
					<a href="<?php the_permalink(); ?>" class="block mb-4">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'negarin-grid-2', array( 'class' => 'w-full h-auto object-cover' ) ); ?>
						<?php else : ?>
							<div class="w-full aspect-[4/5] bg-negarin-cream"></div>
						<?php endif; ?>
					</a>
					<div class="flex items-center gap-3 text-xs opacity-60 mb-2 justify-end">
						<span><?php printf( esc_html__( '%d دقیقه مطالعه', 'negarin' ), negarin_reading_time( get_the_content() ) ); ?></span>
						<span>·</span>
						<span><?php echo esc_html( get_the_date() ); ?></span>
					</div>
					<h2 class="font-serif text-xl mb-2">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<div class="opacity-70 text-sm leading-7"><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile; ?>
		</div>

		<div class="mt-14">
			<?php
			the_posts_pagination(
				array(
					'prev_text' => '‹',
					'next_text' => '›',
					'class'     => 'negarin-pagination',
				)
			);
			?>
		</div>

	<?php else : ?>

		<p class="text-center opacity-60"><?php esc_html_e( 'مطلبی یافت نشد.', 'negarin' ); ?></p>

	<?php endif; ?>

</div>

<?php get_footer(); ?>
