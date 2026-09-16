<?php
/**
 * Server-side render for negarin/icon-button.
 *
 * Wired via block.json's "render" key, so WordPress includes this file
 * automatically with $attributes / $content / $block already in scope —
 * no render_callback registration needed.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Unused; this is a fully dynamic block.
 * @var WP_Block $block      Block instance.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text          = isset( $attributes['text'] ) ? trim( (string) $attributes['text'] ) : '';
$url           = isset( $attributes['url'] ) ? (string) $attributes['url'] : '';
$icon_key      = isset( $attributes['icon'] ) ? (string) $attributes['icon'] : 'none';
$icon_position = ( isset( $attributes['iconPosition'] ) && 'before' === $attributes['iconPosition'] ) ? 'before' : 'after';
$variant       = isset( $attributes['variant'] ) ? (string) $attributes['variant'] : 'solid';
$link_target   = isset( $attributes['linkTarget'] ) ? (string) $attributes['linkTarget'] : '_self';
$rel           = isset( $attributes['rel'] ) ? (string) $attributes['rel'] : '';

if ( '' === $text ) {
	return;
}

$variant_classes = array(
	'outline'       => 'btn--outline',
	'outline-white' => 'btn--outline-white',
);
$variant_class = $variant_classes[ $variant ] ?? 'btn--solid';

$icon_svg = class_exists( '\Negarin\Services\IconButtonBlock' )
	? \Negarin\Services\IconButtonBlock::icon_svg( $icon_key )
	: '';

if ( '_blank' === $link_target && '' === $rel ) {
	$rel = 'noopener noreferrer';
}

$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'negarin-icon-btn-wrap' ) );
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( '' !== $url ) : ?>
		<a
			href="<?php echo esc_url( $url ); ?>"
			<?php echo '_blank' === $link_target ? 'target="_blank"' : ''; ?>
			<?php echo $rel ? 'rel="' . esc_attr( $rel ) . '"' : ''; ?>
			class="btn <?php echo esc_attr( $variant_class ); ?> negarin-icon-btn gap-2"
		>
			<?php if ( $icon_svg && 'before' === $icon_position ) : ?>
				<span class="negarin-icon-btn__icon" aria-hidden="true"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
			<span class="negarin-icon-btn__label"><?php echo esc_html( $text ); ?></span>
			<?php if ( $icon_svg && 'after' === $icon_position ) : ?>
				<span class="negarin-icon-btn__icon" aria-hidden="true"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
		</a>
	<?php else : ?>
		<span class="btn <?php echo esc_attr( $variant_class ); ?> negarin-icon-btn gap-2">
			<?php if ( $icon_svg && 'before' === $icon_position ) : ?>
				<span class="negarin-icon-btn__icon" aria-hidden="true"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
			<span class="negarin-icon-btn__label"><?php echo esc_html( $text ); ?></span>
			<?php if ( $icon_svg && 'after' === $icon_position ) : ?>
				<span class="negarin-icon-btn__icon" aria-hidden="true"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
		</span>
	<?php endif; ?>
</div>
