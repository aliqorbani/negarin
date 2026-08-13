<?php
/**
 * Kavenegar OTP lookup gateway (shipped as the default provider since it is
 * the most common choice for Iranian projects). Uses the 'lookup' endpoint
 * with a pre-approved template so delivery is instant, matching the
 * high-priority OTP routing pattern already used elsewhere in this project.
 *
 * Credentials are pulled from wp-config constants — never hardcode keys.
 * define( 'NEGARIN_KAVENEGAR_API_KEY', '...' );
 * define( 'NEGARIN_KAVENEGAR_OTP_TEMPLATE', 'negarin-otp' );
 *
 * @package Negarin
 */

namespace Negarin\Services\Sms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KavenegarGateway implements SmsGatewayInterface {

	private const API_BASE = 'https://api.kavenegar.com/v1/';

	public function send_otp( string $phone, string $code ): bool {
		$api_key = defined( 'NEGARIN_KAVENEGAR_API_KEY' ) ? NEGARIN_KAVENEGAR_API_KEY : '';

		if ( empty( $api_key ) ) {
			error_log( 'Negarin: NEGARIN_KAVENEGAR_API_KEY is not defined in wp-config.php' ); // phpcs:ignore
			return false;
		}

		$template = defined( 'NEGARIN_KAVENEGAR_OTP_TEMPLATE' ) ? NEGARIN_KAVENEGAR_OTP_TEMPLATE : 'negarin-otp';

		$url = self::API_BASE . $api_key . '/verify/lookup.json?' . http_build_query(
			array(
				'receptor' => $phone,
				'token'    => $code,
				'template' => $template,
			)
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 8,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'Negarin Kavenegar error: ' . $response->get_error_message() ); // phpcs:ignore
			return false;
		}

		$code_status = wp_remote_retrieve_response_code( $response );

		return $code_status >= 200 && $code_status < 300;
	}
}
