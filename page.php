<?php
/**
 * Default page template (used when a Page does NOT use the "Page Builder" template).
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$is_bare_login_screen = function_exists( 'is_account_page' )
        && is_account_page()
        && ! is_user_logged_in();
get_header();
?>
<main id="main-content" class="container mx-auto px-4 py-4">
	<?php
    while ( have_posts() ) :
        the_post();
        ?>

        <article <?php post_class(); ?>>

            <?php if ( ! $is_bare_login_screen ) : ?>
                <h1 class="font-serif text-3xl mb-6">
                    <?php the_title(); ?>
                </h1>
            <?php endif; ?>

            <div class="prose max-w-none leading-8">
                <?php the_content(); ?>
            </div>

        </article>

    <?php endwhile; ?>
</main>
<?php
get_footer();
