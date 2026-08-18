<?php
/**
 * Section: Image + Text, side switchable per-instance from the editor.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$side       = get_sub_field( 'image_side' ) ?: 'right';
$image      = get_sub_field( 'it_image' );
$image_link = get_sub_field( 'it_image_link' );
//$eyebrow    = get_sub_field( 'it_eyebrow' );
$title      = get_sub_field( 'it_title' );
$text       = get_sub_field( 'it_text' );
$button     = get_sub_field( 'it_button' );
$bg         = get_sub_field( 'background_color' ) ?: 'white';

$is_image_left = 'left' === $side;
$has_image_link = is_array( $image_link ) && ! empty( $image_link['url'] );
$image_tag       = $has_image_link ? 'a' : 'div';
?>
<section class="negarin-page-section negarin-image-text <?php echo esc_attr( negarin_section_bg_class( $bg ) ); ?>">
    <div class="container max-w-7xl mx-auto px-4 grid grid-cols-2 gap-10 items-center">

        <div class="<?php echo esc_attr( $is_image_left ? 'order-2' : 'order-1' ); ?>">
            <<?php echo esc_html( $image_tag ); ?>
            <?php if ( $has_image_link ) : ?>
                href="<?php echo esc_url( $image_link['url'] ); ?>"
                <?php echo ! empty( $image_link['target'] ) ? 'target="_blank" rel="noopener"' : ''; ?>
            <?php endif; ?>
            class="relative overflow-hidden group block"
            >
            <?php negarin_image( $image, 'negarin-section-half', 'w-full h-auto object-cover rounded-sm transition-transform duration-500 group-hover:scale-105' ); ?>
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/50 transition-colors duration-500"></div>
            <?php if ( $title ) : ?>
                <span class="md:absolute inset-0 flex items-center justify-center px-4 text-center md:text-white text-sm md:text-2xl font-serif md:opacity-0 md:group-hover:opacity-100 md:transition-opacity md:duration-500">
                        <?php echo esc_html( $title ); ?>
                    </span>
            <?php endif; ?>
        </<?php echo esc_html( $image_tag ); ?>>
    </div>

    <div class="<?php echo esc_attr( $is_image_left ? 'order-1 text-right' : 'order-2 text-right' ); ?> px-2">
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