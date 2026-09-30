<?php
/**
 * MelliPayamak SMS gateway — two send methods against two different
 * MelliPayamak endpoints:
 *
 * - send_otp(): the `send/shared` endpoint with a pre-approved body
 *   (pattern) whose single variable is filled with our own OTP code —
 *   keeps code generation on our side and matches SmsGatewayInterface
 *   exactly, the same way KavenegarGateway does with its lookup template.
 *   MelliPayamak's other OTP endpoint (`send/otp`) generates the code
 *   itself and is NOT compatible with this interface without changing
 *   OtpAuth's issue/verify flow, so it is intentionally not used here.
 * - send_message(): the `send/simple` endpoint for anything that isn't
 *   an approved pattern — an order-confirmation text, an installment
 *   reminder, a "new products" newsletter blast, etc. — where the full
 *   text is written per recipient rather than filled into a template.
 *   Not part of SmsGatewayInterface (that contract is OTP-only); called
 *   directly wherever this kind of custom message is needed.
 *
 * Both send methods go through send_curl(), a single place that POSTs
 * JSON and turns the raw curl result into a success/failure verdict.
 *
 * Every credential and bodyId is read through MelliPayamakConfig, never
 * a raw wp-config constant — see that class for where each value lives.
 *
 * @package Negarin
 */

namespace Negarin\Services\Sms;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MelliPayamakGateway implements SmsGatewayInterface {

    private const API_BASE        = 'https://console.melipayamak.com/api/send/shared/';
    private const SIMPLE_API_BASE = 'https://console.melipayamak.com/api/send/simple/';

    private const CONNECT_TIMEOUT = 5;
    private const TIMEOUT         = 15;

    private MelliPayamakConfig $config;

    public function __construct( ?MelliPayamakConfig $config = null ) {
        $this->config = $config ?? new MelliPayamakConfig();
    }

    public function send_otp( string $phone, string $code ): bool {
        $api_key = $this->config->api_key();
        $body_id = $this->config->otp_body_id();

        if ( '' === $api_key || '' === $body_id ) {
            return false; // MelliPayamakConfig has already logged which one is missing.
        }

        $result = $this->send_curl(
            self::API_BASE . $api_key,
            array(
                'bodyId' => $body_id,
                'to'     => $phone,
                'args'   => array( $code ),
            )
        );

        if ( ! $result['success'] ) {
            error_log( 'Negarin MelliPayamak OTP send failed: ' . $this->describe_failure( $result ) ); // phpcs:ignore
        }

        return $result['success'];
    }

    public function send_order_confirmed( string $phone, array $args ): bool {
        $api_key = $this->config->api_key();
        $body_id = $this->config->order_confirmed_body_id();

        if ( '' === $api_key || '' === $body_id ) {
            return false; // MelliPayamakConfig has already logged which one is missing.
        }

        $result = $this->send_curl(
            self::API_BASE . $api_key,
            array(
                'bodyId' => $body_id,
                'to'     => $phone,
                'args'   => (array) $args,
            )
        );

        if ( ! $result['success'] ) {
            error_log( 'Negarin MelliPayamak OTP send failed: ' . $this->describe_failure( $result ) ); // phpcs:ignore
        }

        return $result['success'];
    }

    public function send_installment_reminder( string $phone, array $args ): bool {
        $api_key = $this->config->api_key();
        $body_id = $this->config->installment_reminder_body_id();

        if ( '' === $api_key || '' === $body_id ) {
            return false; // MelliPayamakConfig has already logged which one is missing.
        }

        $result = $this->send_curl(
            self::API_BASE . $api_key,
            array(
                'bodyId' => $body_id,
                'to'     => $phone,
                'args'   => (array) $args,
            )
        );

        if ( ! $result['success'] ) {
            error_log( 'Negarin MelliPayamak OTP send failed: ' . $this->describe_failure( $result ) ); // phpcs:ignore
        }

        return $result['success'];
    }

    /**
     * Sends a fully custom SMS — no bodyId/pattern involved, $text is
     * whatever the caller composed for that one recipient (an order
     * confirmation, an installment reminder, etc.).
     *
     * @param string $phone Local phone number, e.g. 09121234567.
     * @param string $text  The full message body to send as-is.
     */
    public function send_message( string $phone, string $text ): bool {
        $api_key = $this->config->api_key();
        $sender  = $this->config->sender();

        if ( '' === $api_key || '' === $sender ) {
            return false; // MelliPayamakConfig has already logged which one is missing.
        }

        $result = $this->send_curl(
            self::SIMPLE_API_BASE . $api_key,
            array(
                'from' => $sender,
                'to'   => $phone,
                'text' => $text,
            )
        );

        if ( ! $result['success'] ) {
            error_log( 'Negarin MelliPayamak message send failed: ' . $this->describe_failure( $result ) ); // phpcs:ignore
        }

        return $result['success'];
    }

    /**
     * POSTs $payload as JSON to $url and reports what happened. Returns
     * a small result array instead of a single bool/response-body value
     * so a caller (or the error_log() lines above) can always tell a
     * rejected request (HTTP status outside 2xx) apart from a transport
     * failure (DNS, timeout, TLS) — both send_otp() and send_message()
     * only read `success`, but that distinction is cheap to keep around
     * for logging and costs nothing extra to compute.
     *
     * SSL peer verification is left OFF, matching MelliPayamak's own
     * sample and this gateway's previous behavior — OTP delivery already
     * depends on this working as-is on this host, so this refactor
     * doesn't change it. Revisit once the server's CA bundle is
     * confirmed fine (or switch to wp_remote_post(), which ships its own
     * bundle and is what KavenegarGateway already uses).
     *
     * @return array{success: bool, status_code: ?int, body: ?string, error: ?string}
     */
    private function send_curl( string $url, array $payload ): array {
        $data_string = wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_string );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT );
        curl_setopt( $ch, CURLOPT_TIMEOUT, self::TIMEOUT );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen( $data_string ),
            )
        );

        $body        = curl_exec( $ch );
        $status_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $curl_error  = curl_error( $ch ); // Read before curl_close() — a closed handle reports no error at all.
        curl_close( $ch );

        if ( false === $body || '' !== $curl_error ) {
            return array(
                'success'     => false,
                'status_code' => null,
                'body'        => null,
                'error'       => '' !== $curl_error ? $curl_error : 'curl_exec returned false with no error message',
            );
        }

        return array(
            'success'     => $status_code >= 200 && $status_code < 300,
            'status_code' => $status_code,
            'body'        => $body,
            'error'       => null,
        );
    }

    /**
     * @param array{success: bool, status_code: ?int, body: ?string, error: ?string} $result
     */
    private function describe_failure( array $result ): string {
        if ( null !== $result['error'] ) {
            return 'cURL error: ' . $result['error'];
        }

        return 'HTTP ' . $result['status_code'] . ': ' . $result['body'];
    }
}
