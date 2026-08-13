<?php
/**
 * Development SMS gateway.
 *
 * Logs OTP codes instead of sending SMS.
 *
 * @package Negarin
 */

namespace Negarin\Services\Sms;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LogGateway implements SmsGatewayInterface {

    public function send_otp( string $phone, string $code ): bool {
        $log = sprintf(
            'Negarin OTP TEST: phone=%s code=%s',
            $phone,
            $code
        );
        file_put_contents(__DIR__.'/otps.log',$log.PHP_EOL,FILE_APPEND);
        error_log(
            $log
        );

        return true;
    }
}