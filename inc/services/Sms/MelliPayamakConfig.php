<?php
/**
 * MelliPayamak configuration — every value MelliPayamakGateway needs,
 * from two different kinds of source:
 *
 *  - Credentials (api_key, sender): real secrets, environment-specific —
 *    these stay wp-config.php constants ONLY, never hardcoded here.
 *    define( 'NEGARIN_MELLIPAYAMAK_API_KEY', '...' );
 *    define( 'NEGARIN_MELLIPAYAMAK_SENDER', '...' ); // only send_message() needs this
 *
 *  - Body IDs (otp, order_confirmed, installment_reminder): each one is
 *    just a reference to a pre-approved pattern in the MelliPayamak
 *    panel — useless on its own without the API key above, so there is
 *    little reason to keep it out of version control the way a real
 *    credential needs to be. Fill in the values as class constants
 *    below directly.
 *
 *    NEGARIN_OTP_BODY_ID already exists in wp-config.php on the live
 *    site, so it keeps working exactly as before: a wp-config.php
 *    constant, if defined, always overrides the class constant below it
 *    with the same name — no wp-config.php edit is needed for the OTP
 *    id to keep working. The two new ones (order_confirmed,
 *    installment_reminder) have no such constant yet, so just fill in
 *    their class constant here; add a matching wp-config.php constant
 *    later only if a given environment ever needs a different value.
 *
 * @package Negarin
 */

namespace Negarin\Services\Sms;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MelliPayamakConfig {

    // Fill in the real bodyId from the MelliPayamak panel for each.
    private const OTP_BODY_ID                  = '';
    private const ORDER_CONFIRMED_BODY_ID      = '';
    private const INSTALLMENT_REMINDER_BODY_ID = '';

    public function api_key(): string {
        return $this->wp_config_constant( 'NEGARIN_MELLIPAYAMAK_API_KEY' );
    }

    public function sender(): string {
        return $this->wp_config_constant( 'NEGARIN_MELLIPAYAMAK_SENDER' );
    }

    public function otp_body_id(): string {
        return $this->body_id( 'NEGARIN_OTP_BODY_ID', self::OTP_BODY_ID, 'otp' );
    }

    public function order_confirmed_body_id(): string {
        return $this->body_id( 'NEGARIN_ORDER_CONFIRMED_BODY_ID', self::ORDER_CONFIRMED_BODY_ID, 'order_confirmed' );
    }

    public function installment_reminder_body_id(): string {
        return $this->body_id( 'NEGARIN_INSTALLMENT_REMINDER_BODY_ID', self::INSTALLMENT_REMINDER_BODY_ID, 'installment_reminder' );
    }

    /**
     * True once both the API key and the OTP body ID are available —
     * everything OtpAuth's gateway auto-detection needs to know before
     * choosing MelliPayamakGateway as the active provider.
     */
    public function is_ready_for_otp(): bool {
        return '' !== $this->api_key() && '' !== $this->otp_body_id();
    }

    private function body_id( string $override_constant, string $default, string $label ): string {
        if ( defined( $override_constant ) && '' !== trim( (string) constant( $override_constant ) ) ) {
            return (string) constant( $override_constant );
        }

        if ( '' === trim( $default ) ) {
            error_log( "Negarin: MelliPayamak '{$label}' body ID is not set — fill in MelliPayamakConfig::" . strtoupper( $label ) . "_BODY_ID or define {$override_constant} in wp-config.php" ); // phpcs:ignore
            return '';
        }

        return $default;
    }

    private function wp_config_constant( string $name ): string {
        if ( ! defined( $name ) || '' === trim( (string) constant( $name ) ) ) {
            error_log( "Negarin: {$name} is not defined in wp-config.php" ); // phpcs:ignore
            return '';
        }

        return (string) constant( $name );
    }
}
