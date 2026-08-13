<?php
/**
 * Footer "برای نگارین بنویسید" mini contact form. Not a full contact page
 * (that's a separate future page) — just a quick one-field message sender
 * matching the footer design.
 *
 * @package Negarin
 */

namespace Negarin\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FooterMessage {

	public function __construct() {
		add_action( 'admin_post_negarin_footer_message', array( $this, 'handle_submit' ) );
		add_action( 'admin_post_nopriv_negarin_footer_message', array( $this, 'handle_submit' ) );
	}

	public function handle_submit(): void {
		check_admin_referer( 'negarin_footer_message' );

		$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
		$referer = wp_get_referer() ?: home_url( '/' );

		if ( '' === trim( $message ) ) {
			wp_safe_redirect( add_query_arg( 'negarin_msg', 'empty', $referer ) );
			exit;
		}

		// Simple honeypot: a hidden field a bot would fill in, a human never would.
		if ( ! empty( $_POST['website'] ) ) {
			wp_safe_redirect( add_query_arg( 'negarin_msg', 'sent', $referer ) );
			exit;
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

		wp_safe_redirect( add_query_arg( 'negarin_msg', $sent ? 'sent' : 'failed', $referer ) );
		exit;
	}
}
