<?php
/**
 * Self-hosted image CAPTCHA — no Google reCAPTCHA/hCaptcha dependency.
 * Renders a distorted numeric code as a PNG (plain GD, no TTF font file),
 * keyed by a one-time token stored in a transient.
 *
 * GET  /wp-json/negarin/v1/captcha  -> { token, image } (image: base64 PNG data URI)
 * verify( $token, $answer )         -> bool, and always consumes the token
 *                                      (one-time use, matches the front-end's
 *                                      "fetch a fresh captcha after every
 *                                      submit" behaviour — assets/js/contact-form.js)
 *
 * Currently only used by inc/services/ContactForm.php, but verify() is
 * public/static so any other form can reuse the same captcha later.
 *
 * @package Negarin
 */

namespace Negarin\Services;

use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Captcha {

    private const CODE_LENGTH = 5;
    private const TTL         = 10 * MINUTE_IN_SECONDS;
    private const WIDTH       = 170;
    private const HEIGHT      = 60;

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes(): void {
        register_rest_route(
            'negarin/v1',
            '/captcha',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'handle_issue' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * @return WP_REST_Response|WP_Error
     */
    public function handle_issue() {

        if ( ! extension_loaded( 'gd' ) ) {
            return new WP_Error( 'negarin_captcha_unavailable', __( 'قابلیت کد امنیتی روی این سرور در دسترس نیست.', 'negarin' ), array( 'status' => 500 ) );
        }

        $code  = self::random_code();
        $token = wp_generate_password( 32, false );

        set_transient( 'negarin_captcha_' . $token, $code, self::TTL );

        $response = new WP_REST_Response(
            array(
                'token' => $token,
                'image' => 'data:image/png;base64,' . base64_encode( self::render_image( $code ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- raw image bytes, not obfuscated code.
            ),
            200
        );

        $response->header(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, max-age=0, private'
        );
        $response->header( 'Pragma', 'no-cache' );
        $response->header( 'Expires', '0' );

        return $response;
    }

    /**
     * Checks $answer against the code stored for $token and, either way,
     * deletes the transient — a token is only ever good for one attempt.
     */
    public static function verify( string $token, string $answer ): bool {
        $token = sanitize_text_field( $token );
        if ( '' === $token ) {
            return false;
        }

        $key      = 'negarin_captcha_' . $token;
        $expected = get_transient( $key );
        delete_transient( $key );

        if ( false === $expected ) {
            return false; // Expired, already used, or never issued.
        }

        return hash_equals( (string) $expected, trim( (string) $answer ) );
    }

    private static function random_code(): string {
        $code = '';
        for ( $i = 0; $i < self::CODE_LENGTH; $i++ ) {
            $code .= (string) wp_rand( 0, 9 );
        }
        return $code;
    }

    /**
     * Plain GD, no TTF font file — imagestring()'s built-in bitmap fonts
     * keep this dependency-free, at the cost of characters that can't be
     * rotated/skewed. Kept light on noise on purpose: anything heavier
     * started fighting with the (already thin) bitmap-font digits for
     * legibility in testing. Good enough to stop casual scripted spam,
     * not a hardened anti-bot measure.
     */
    private static function render_image( string $code ): string {
        $image = imagecreatetruecolor( self::WIDTH, self::HEIGHT );
        $bg    = imagecolorallocate( $image, 249, 250, 251 );
        imagefilledrectangle( $image, 0, 0, self::WIDTH, self::HEIGHT, $bg );

        for ( $i = 0; $i < 3; $i++ ) {
            $line = imagecolorallocate( $image, wp_rand( 215, 235 ), wp_rand( 215, 235 ), wp_rand( 215, 235 ) );
            imageline( $image, wp_rand( 0, self::WIDTH ), wp_rand( 0, self::HEIGHT ), wp_rand( 0, self::WIDTH ), wp_rand( 0, self::HEIGHT ), $line );
        }

        for ( $i = 0; $i < 80; $i++ ) {
            $dot = imagecolorallocate( $image, wp_rand( 210, 235 ), wp_rand( 210, 235 ), wp_rand( 210, 235 ) );
            imagesetpixel( $image, wp_rand( 0, self::WIDTH ), wp_rand( 0, self::HEIGHT ), $dot );
        }

        $chars   = str_split( $code );
        $spacing = intdiv( self::WIDTH, count( $chars ) + 1 );

        foreach ( $chars as $i => $char ) {
            $color = imagecolorallocate( $image, wp_rand( 20, 70 ), wp_rand( 20, 70 ), wp_rand( 20, 70 ) );
            $x     = $spacing * ( $i + 1 ) - 6 + wp_rand( -3, 3 );
            $y     = wp_rand( 14, 20 );
            // Drawn twice with a 1px offset to fake a bolder weight —
            // GD's built-in bitmap font (font size 5, the largest
            // available to imagestring()) is otherwise quite thin.
            imagestring( $image, 5, (int) $x + 1, (int) $y, $char, $color );
            imagestring( $image, 5, (int) $x, (int) $y, $char, $color );
        }

        ob_start();
        imagepng( $image );
        $bytes = ob_get_clean();
        imagedestroy( $image );

        return (string) $bytes;
    }
}