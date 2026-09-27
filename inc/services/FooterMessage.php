<?php
/**
 * Footer "برای نگارین بنویسید" mini contact form. Not the full contact
 * page (that's templates/page-contact.php) — just a quick one-field
 * message sender matching the footer design. AJAX-only
 * (assets/js/footer-message.js, template-parts/footer/site-footer.php) —
 * no full-page POST/redirect fallback.
 *
 * POST /wp-json/negarin/v1/footer-message  body: { message, website }
 *
 * @package Negarin
 */

namespace Negarin\Services;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FooterMessage {

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes(): void {
        register_rest_route(
            'negarin/v1',
            '/footer-message',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_submit' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * @return WP_REST_Response|WP_Error
     */
    public function handle_submit( WP_REST_Request $request ) {
        $message = sanitize_textarea_field( (string) $request->get_param( 'message' ) );

        if ( '' === trim( $message ) ) {
            return new WP_Error( 'negarin_footer_message_empty', __( 'لطفاً پیام خود را بنویسید.', 'negarin' ), array( 'status' => 400 ) );
        }

        // Simple honeypot: a hidden field a bot would fill in, a human never would.
        // Pretend success so a bot never learns it was caught.
        if ( ! empty( $request->get_param( 'website' ) ) ) {
            return new WP_REST_Response( array( 'success' => true ), 200 );
        }

        $sent = wp_mail(
            get_option( 'admin_email' ),
            sprintf(
            /* translators: %s: site name */
                __( 'پیام جدید از فوتر سایت %s', 'negarin' ),
                get_bloginfo( 'name' )
            ),
            $message
        );

        if ( ! $sent ) {
            return new WP_Error( 'negarin_footer_message_failed', __( 'ارسال پیام با خطا مواجه شد.', 'negarin' ), array( 'status' => 500 ) );
        }

        return new WP_REST_Response( array( 'success' => true ), 200 );
    }
}