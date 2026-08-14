<?php
/**
 * Section: Image + Text, side switchable per-instance from the editor.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$side    = get_sub_field( 'image_side' ) ?: 'right';
$image   = get_sub_field( 'it_image' );
$eyebrow = get_sub_field( 'it_eyebrow' );
$title   = get_sub_field( 'it_title' );
$text    = get_sub_field( 'it_text' );
$button  = get_sub_field( 'it_button' );
$bg      = get_sub_field( 'background_color' ) ?: 'white';

$is_image_left = 'left' === $side;
?>
<section class="negarin-image-text py-12 md:py-20 <?php echo esc_attr( negarin_section_bg_class( $bg ) ); ?>">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">

        <div class="<?php echo esc_attr( $is_image_left ? 'md:order-2' : 'md:order-1' ); ?>">
            <div class="relative overflow-hidden group">
                <?php negarin_image( $image, 'negarin-section-half', 'w-full h-auto object-cover rounded-sm transition-transform duration-500 group-hover:scale-105' ); ?>
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors duration-500"></div>
                <?php if ( $title ) : ?>
                    <span class="absolute inset-0 flex items-center justify-center px-4 text-center text-white text-xl md:text-2xl font-serif opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                        <?php echo esc_html( $title ); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="<?php echo esc_attr( $is_image_left ? 'md:order-1 text-right' : 'md:order-2 text-right' ); ?> px-2">
            <?php /*if ( $eyebrow ) : */?><!--
                <span class="block text-sm text-negarin-gold mb-2"><?php /*echo esc_html( $eyebrow ); */?></span>
            <?php /*endif; */?>
            <?php /*if ( $title ) : */?>
                <h3 class="font-serif text-2xl md:text-3xl mb-4"><?php /*echo esc_html( $title ); */?></h3>
            --><?php /*endif; */?>
            <?php if ( $text ) : ?>
                <p class="section-image-text"><?php echo esc_html( $text ); ?></p>
            <?php endif; ?>
            <?php negarin_link_button( $button, 'btn btn--outline' ); ?>
        </div>

    </div>
</section>