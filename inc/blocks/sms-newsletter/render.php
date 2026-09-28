<?php
/**
 * Server-side render for negarin/sms-newsletter.
 *
 * Wired via block.json's "render" key, so WordPress includes this file
 * automatically with $attributes already in scope — no render_callback
 * registration needed. Just a thin wrapper around the same
 * template-parts/components/sms-newsletter-form.php used by the
 * [negarin_sms_newsletter] shortcode (inc/services/SmsNewsletter.php),
 * so the block and the shortcode can never drift apart.
 *
 * @var array $attributes Block attributes.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$title       = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$description = isset( $attributes['description'] ) ? (string) $attributes['description'] : '';
$button_text = isset( $attributes['buttonText'] ) && '' !== trim( (string) $attributes['buttonText'] )
    ? (string) $attributes['buttonText']
    : __( 'عضویت', 'negarin' );

$wrapper_attributes = get_block_wrapper_attributes();
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <?php include NEGARIN_DIR . '/template-parts/components/sms-newsletter-form.php'; ?>
</div>
