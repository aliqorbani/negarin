<?php
/**
 * Single blog post.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$toc = negarin_extract_toc( get_the_content() );
	?>

	<article <?php post_class( 'max-w-3xl mx-auto px-4 py-10' ); ?>>

		<?php get_template_part( 'template-parts/components/breadcrumbs' ); ?>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="mb-8">
				<?php the_post_thumbnail( 'negarin-hero', array( 'class' => 'w-full h-auto object-cover' ) ); ?>
			</div>
		<?php endif; ?>

		<h1 class="font-serif text-3xl md:text-4xl mb-4 text-right"><?php the_title(); ?></h1>

		<div class="flex items-center gap-4 text-xs opacity-60 mb-8 justify-end">
			<span><?php printf( esc_html__( '%d دقیقه مطالعه', 'negarin' ), negarin_reading_time( get_the_content() ) ); ?></span>
			<span>·</span>
			<span><?php echo esc_html( get_the_date() ); ?></span>
			<span>·</span>
			<span><?php the_author(); ?></span>
		</div>

		<?php
		get_template_part(
			'template-parts/components/toc',
			null,
			array( 'items' => $toc['items'] )
		);
		?>

		<div class="prose prose-neutral max-w-none leading-8 text-right">
			<?php echo apply_filters( 'the_content', $toc['content'] ); // phpcs:ignore ?>
		</div>

		<?php
		$tags = get_the_tags();
		if ( $tags ) :
			?>
			<div class="flex flex-wrap gap-2 mt-8 justify-end">
				<?php foreach ( $tags as $tag ) : ?>
					<a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>" class="text-xs border border-black/10 rounded-full px-3 py-1">
						#<?php echo esc_html( $tag->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/components/related-products' ); ?>

		<?php
		$related = new WP_Query(
			array(
				'category__in'   => wp_get_post_categories( get_the_ID() ),
				'post__not_in'   => array( get_the_ID() ),
				'posts_per_page' => 3,
				'ignore_sticky_posts' => true,
			)
		);
		?>
		<?php if ( $related->have_posts() ) : ?>
			<section class="mt-16 pt-10 border-t border-black/10">
				<h2 class="font-serif text-xl mb-6 text-right"><?php esc_html_e( 'مطالب مرتبط', 'negarin' ); ?></h2>
				<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
					<?php
					while ( $related->have_posts() ) :
						$related->the_post();
						?>
						<a href="<?php the_permalink(); ?>" class="block text-right">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'negarin-grid-3', array( 'class' => 'w-full h-auto object-cover mb-3' ) ); ?>
							<?php endif; ?>
							<h3 class="text-sm"><?php the_title(); ?></h3>
						</a>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>

	</article>

<?php endwhile; ?>

<?php get_footer(); ?>
