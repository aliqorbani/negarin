<?php
/**
 * MelliPayamak pattern-based ("shared") SMS gateway.
 *
 * Uses the `send/shared` endpoint with a pre-approved body (pattern) whose
 * single variable is filled with our own OTP code — this keeps code
 * generation on our side and matches SmsGatewayInterface exactly, the same
 * way KavenegarGateway does with its lookup template. MelliPayamak's other
 * endpoint (`send/otp`) generates the code itself and is NOT compatible
 * with this interface without changing OtpAuth's issue/verify flow, so it
 * is intentionally not used here.
 *
 * Credentials are pulled from wp-config constants — never hardcode keys.
 * define( 'NEGARIN_MELLIPAYAMAK_API_KEY', '...' );
 * define( 'NEGARIN_OTP_BODY_ID', '...' ); // approved pattern ("bodyId") in the MelliPayamak panel; its {0} placeholder receives the OTP code.
 *
 * @package Negarin
 */

namespace Negarin\Services\Sms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MelliPayamakGateway implements SmsGatewayInterface {

	private const API_BASE = 'https://console.melipayamak.com/api/send/shared/';

	public function send_otp( string $phone, string $code ): bool {
		$api_key = defined( 'NEGARIN_MELLIPAYAMAK_API_KEY' ) ? NEGARIN_MELLIPAYAMAK_API_KEY : '';

//        error_log('api_key: ' . $api_key);

		if ( empty( $api_key ) ) {
			error_log( 'Negarin: NEGARIN_MELLIPAYAMAK_API_KEY is not defined in wp-config.php' ); // phpcs:ignore
			return false;
		}

		$body_id = defined( 'NEGARIN_OTP_BODY_ID' ) ? NEGARIN_OTP_BODY_ID : '';
//        error_log('body_id: ' . $body_id);
		if ( empty( $body_id ) ) {
			error_log( 'Negarin: NEGARIN_OTP_BODY_ID is not defined in wp-config.php' ); // phpcs:ignore
			return false;
		}

		$url = self::API_BASE . $api_key;
//        error_log('url: ' . $url);

        $data = array('bodyId' => $body_id, 'to' => $phone, 'args' => [$code]);
        $data_string = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array('Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $curl = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
//        file_put_contents(__DIR__.'/otps.log', print_r($response, true), FILE_APPEND);
		if ( curl_error($ch) ) {
			error_log( 'Negarin MelliPayamak error: ' . curl_errno($ch) ); // phpcs:ignore
			return false;
		}

		$response_body = $curl;

		// MelliPayamak's documented response shape/error codes for this
		// endpoint were not available to verify at implementation time, so
		// only the HTTP status is used as the success signal — matching
		if ( $status_code < 200 || $status_code >= 300 ) {
			error_log( 'Negarin MelliPayamak HTTP ' . $status_code . ': ' . $response_body ); // phpcs:ignore
			return false;
		}

		return true;
	}
}
