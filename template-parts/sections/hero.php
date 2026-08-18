<?php
/**
 * Section: Hero (full width image, optional overlay text).
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$image    = get_sub_field( 'hero_image' );
$eyebrow  = get_sub_field( 'hero_eyebrow' );
$title    = get_sub_field( 'hero_title' );
$text     = get_sub_field( 'hero_text' );
$button   = get_sub_field( 'hero_button' );
$position = get_sub_field( 'text_position' ) ?: 'none';
?>
<section class="negarin-page-section negarin-hero relative w-full overflow-hidden">
    <?php negarin_image( $image, 'negarin-hero', 'w-full h-auto object-cover', false ); ?>

    <?php if ( 'none' !== $position && ( $title || $text ) ) : ?>
        <div class="negarin-hero__overlay absolute inset-0 flex flex-col items-center justify-<?php echo esc_attr( 'bottom' === $position ? 'end pb-12' : 'center' ); ?> px-6 text-center bg-black/20">
            <?php if ( $eyebrow ) : ?>
                <span class="block tracking-[0.3em] text-xs uppercase text-white/80 mb-2"><?php echo esc_html( $eyebrow ); ?></span>
            <?php endif; ?>
            <?php if ( $title ) : ?>
                <h2 class="font-serif text-3xl md:text-5xl text-white mb-3"><?php echo esc_html( $title ); ?></h2>
            <?php endif; ?>
            <?php if ( $text ) : ?>
                <p class="text-white/90 max-w-xl mb-6"><?php echo esc_html( $text ); ?></p>
            <?php endif; ?>
            <?php negarin_link_button( $button, 'btn btn--outline-white' ); ?>
        </div>
    <?php endif; ?>
</section>
