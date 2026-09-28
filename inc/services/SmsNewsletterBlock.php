<?php
/**
 * Registers the negarin/sms-newsletter Gutenberg block — an editor-only
 * wrapper around the same form used by the [negarin_sms_newsletter]
 * shortcode (inc/services/SmsNewsletter.php,
 * template-parts/components/sms-newsletter-form.php). Same pattern as
 * IconButtonBlock.php: a dynamic block, PHP renders the real markup, the
 * editor JS only edits attributes and previews via ServerSideRender.
 *
 * The "negarin" block category and the block-editor Tailwind CSS are
 * already registered by IconButtonBlock — nothing to duplicate here.
 *
 * @package Negarin
 */

namespace Negarin\Services;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SmsNewsletterBlock {

    public function __construct() {
        add_action( 'init', array( $this, 'register_block' ) );
    }

    public function register_block(): void {
        wp_register_script(
            'negarin-sms-newsletter-editor',
            NEGARIN_URI . '/inc/blocks/sms-newsletter/index.js',
            array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
            NEGARIN_VERSION,
            true
        );

        register_block_type( NEGARIN_DIR . '/inc/blocks/sms-newsletter' );
    }
}
