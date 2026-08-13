<?php
/**
 * Contract every SMS provider must implement. Swap providers by changing
 * the `negarin_sms_gateway` filter — nothing else in the OTP flow changes.
 *
 * @package Negarin
 */

namespace Negarin\Services\Sms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface SmsGatewayInterface {

	/**
	 * Send an OTP code to a phone number.
	 *
	 * @param string $phone E.164-ish local phone number, e.g. 09121234567.
	 * @param string $code  The one-time code to deliver.
	 * @return bool True on accepted-for-delivery, false on failure.
	 */
	public function send_otp( string $phone, string $code ): bool;
}
