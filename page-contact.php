<?php
/**
 * Template Name: تماس با ما
 *
 * Assign this template to a page from Page Attributes. The page's own
 * content stays fully admin-editable (address, phone, map embed, opening
 * hours — anything, via the normal editor) and is shown above the fold;
 * this template only adds the contact form itself
 * (template-parts/components/contact-form.php) below it.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
    <main id="main-content" class="container max-w-7xl mx-auto px-4 py-10 md:py-16">
        <?php
        while ( have_posts() ) :
            the_post();
            ?>
            <h1 class="font-serif text-2xl md:text-3xl text-center mb-10"><?php the_title(); ?></h1>

            <?php if ( get_the_content() ) : ?>
            <div class="max-w-xl mx-auto leading-8 mb-12 text-center">
                <?php the_content(); ?>
            </div>
        <?php endif; ?>
        <?php endwhile; ?>

        <?php get_template_part( 'template-parts/components/contact-form' ); ?>
    </main>
<?php
get_footer();