<?php
/**
 * Phone-number + OTP authentication. No email or username login exists in
 * this theme; wp-login.php is not used by customers at all (see hooks/otp-guards.php).
 *
 * Flow:
 *  POST /wp-json/negarin/v1/otp/request   { phone }             -> sends code, starts rate-limit + expiry window
 *  POST /wp-json/negarin/v1/otp/verify    { phone, code }        -> creates/logs in user, returns auth cookie
 *  POST /wp-json/negarin/v1/otp/resend    { phone }              -> resend, respects countdown
 *
 * @package Negarin
 */

namespace Negarin\Services;

use Negarin\Services\Sms\SmsGatewayInterface;
use Negarin\Services\Sms\MelliPayamakGateway;
use Negarin\Services\Sms\KavenegarGateway;
use Negarin\Services\Sms\LogGateway;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtpAuth {

    private const OTP_TTL_SECONDS      = 120; // code expires after 2 minutes.
    private const RESEND_COOLDOWN      = 60;  // must wait 60s between sends.
    private const MAX_ATTEMPTS_PER_HR  = 6;   // rate limit per phone number.
    private const CODE_LENGTH          = 5;

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Resolve the active SMS gateway. Filterable so any provider can replace
     * the default without touching the OTP flow itself.
     *
     * Auto-detects by configured credentials, in priority order:
     * MelliPayamak (NEGARIN_MELLIPAYAMAK_API_KEY + NEGARIN_OTP_BODY_ID) ->
     * Kavenegar (NEGARIN_KAVENEGAR_API_KEY) -> LogGateway (writes the code
     * to WooCommerce logs + error_log instead of sending a real SMS, so the
     * OTP flow stays testable before any provider credentials exist). Set
     * NEGARIN_OTP_TEST_MODE to true in wp-config.php to force logging even
     * when real credentials are present (useful on staging, to avoid
     * burning SMS credit).
     */
    private function gateway(): SmsGatewayInterface {
        $force_test_mode = defined( 'NEGARIN_OTP_TEST_MODE' ) && NEGARIN_OTP_TEST_MODE;

        if ( $force_test_mode ) {
            $default_gateway = new LogGateway();
        } else {
            $mellipayamak_configured = defined( 'NEGARIN_MELLIPAYAMAK_API_KEY' ) && ! empty( NEGARIN_MELLIPAYAMAK_API_KEY )
                && defined( 'NEGARIN_OTP_BODY_ID' ) && ! empty( NEGARIN_OTP_BODY_ID );
            $kavenegar_configured    = defined( 'NEGARIN_KAVENEGAR_API_KEY' ) && ! empty( NEGARIN_KAVENEGAR_API_KEY );

            if ( $mellipayamak_configured ) {
                $default_gateway = new MelliPayamakGateway();
            } elseif ( $kavenegar_configured ) {
                $default_gateway = new KavenegarGateway();
            } else {
                $default_gateway = new LogGateway();
            }
        }

        /**
         * @param SmsGatewayInterface $gateway
         */
        return apply_filters( 'negarin_sms_gateway', $default_gateway );
    }

    public function register_routes(): void {
        register_rest_route(
            'negarin/v1',
            '/otp/request',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_request' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'phone' => array( 'required' => true ),
                ),
            )
        );

        register_rest_route(
            'negarin/v1',
            '/otp/resend',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_resend' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'phone' => array( 'required' => true ),
                ),
            )
        );

        register_rest_route(
            'negarin/v1',
            '/otp/verify',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_verify' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'phone' => array( 'required' => true ),
                    'code'  => array( 'required' => true ),
                ),
            )
        );
    }

    private function normalize_phone( string $phone ): ?string {
        $phone = preg_replace( '/\D/', '', $phone );
        if ( $phone && str_starts_with( $phone, '0' ) ) {
            return $phone;
        }
        if ( $phone && str_starts_with( $phone, '98' ) ) {
            return '0' . substr( $phone, 2 );
        }
        return ( $phone && strlen( $phone ) === 10 ) ? '0' . $phone : null;
    }

    public function handle_request( WP_REST_Request $request ) {
        $phone = $this->normalize_phone( (string) $request->get_param( 'phone' ) );

        if ( ! $phone || ! preg_match( '/^09\d{9}$/', $phone ) ) {
            return new WP_Error( 'invalid_phone', __( 'شماره موبایل نامعتبر است.', 'negarin' ), array( 'status' => 422 ) );
        }

        $rate_key = 'negarin_otp_rate_' . $phone;
        $attempts = (int) get_transient( $rate_key );

        if ( $attempts >= self::MAX_ATTEMPTS_PER_HR ) {
            return new WP_Error( 'rate_limited', __( 'تعداد درخواست‌های شما بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.', 'negarin' ), array( 'status' => 429 ) );
        }

        return $this->issue_code( $phone, $rate_key, $attempts );
    }

    public function handle_resend( WP_REST_Request $request ) {
        $phone = $this->normalize_phone( (string) $request->get_param( 'phone' ) );

        if ( ! $phone ) {
            return new WP_Error( 'invalid_phone', __( 'شماره موبایل نامعتبر است.', 'negarin' ), array( 'status' => 422 ) );
        }

        if ( get_transient( 'negarin_otp_cooldown_' . $phone ) ) {
            return new WP_Error( 'cooldown', __( 'لطفاً کمی صبر کنید و دوباره تلاش کنید.', 'negarin' ), array( 'status' => 429 ) );
        }

        $rate_key = 'negarin_otp_rate_' . $phone;
        $attempts = (int) get_transient( $rate_key );

        if ( $attempts >= self::MAX_ATTEMPTS_PER_HR ) {
            return new WP_Error( 'rate_limited', __( 'تعداد درخواست‌های شما بیش از حد مجاز است.', 'negarin' ), array( 'status' => 429 ) );
        }

        return $this->issue_code( $phone, $rate_key, $attempts );
    }

    private function issue_code( string $phone, string $rate_key, int $attempts ) {
        $code = (string) wp_rand( (int) str_pad( '1', self::CODE_LENGTH, '0' ), (int) str_pad( '9', self::CODE_LENGTH, '9' ) );

        set_transient( 'negarin_otp_code_' . $phone, wp_hash_password( $code ), self::OTP_TTL_SECONDS );
        set_transient( 'negarin_otp_cooldown_' . $phone, 1, self::RESEND_COOLDOWN );
        set_transient( $rate_key, $attempts + 1, HOUR_IN_SECONDS );

        $sent = $this->gateway()->send_otp( $phone, $code );

        if ( ! $sent ) {
            return new WP_Error( 'sms_failed', __( 'ارسال پیامک با خطا مواجه شد. دوباره تلاش کنید.', 'negarin' ), array( 'status' => 502 ) );
        }

        return new WP_REST_Response(
            array(
                'success'          => true,
                'expires_in'       => self::OTP_TTL_SECONDS,
                'resend_available_in' => self::RESEND_COOLDOWN,
            ),
            200
        );
    }

    public function handle_verify( WP_REST_Request $request ) {
        $phone = $this->normalize_phone( (string) $request->get_param( 'phone' ) );
        $code  = preg_replace( '/\D/', '', (string) $request->get_param( 'code' ) );

        if ( ! $phone || ! $code ) {
            return new WP_Error( 'invalid_input', __( 'اطلاعات ارسالی نامعتبر است.', 'negarin' ), array( 'status' => 422 ) );
        }

        $hashed = get_transient( 'negarin_otp_code_' . $phone );

        if ( ! $hashed ) {
            return new WP_Error( 'expired', __( 'کد منقضی شده است. دوباره درخواست دهید.', 'negarin' ), array( 'status' => 410 ) );
        }

        if ( ! wp_check_password( $code, $hashed ) ) {
            return new WP_Error( 'invalid_code', __( 'کد وارد شده صحیح نیست.', 'negarin' ), array( 'status' => 422 ) );
        }

        delete_transient( 'negarin_otp_code_' . $phone );

        $user = $this->find_or_create_user( $phone );

        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, true );

        return new WP_REST_Response(
            array(
                'success'  => true,
                'user_id'  => $user->ID,
                'redirect' => wc_get_page_permalink( 'myaccount' ) ?: home_url( '/' ),
            ),
            200
        );
    }

    private function find_or_create_user( string $phone ): \WP_User {
        $existing = get_users(
            array(
                'meta_key'   => 'negarin_phone', // phpcs:ignore
                'meta_value' => $phone, // phpcs:ignore
                'number'     => 1,
            )
        );

        if ( ! empty( $existing ) ) {
            return $existing[0];
        }

        $user_id = wp_insert_user(
            array(
                'user_login' => 'user_' . $phone,
                'user_pass'  => wp_generate_password( 20 ),
                'role'       => 'customer',
            )
        );

        update_user_meta( $user_id, 'negarin_phone', $phone );
        update_user_meta( $user_id, 'billing_phone', $phone );

        return get_user_by( 'id', $user_id );
    }
}