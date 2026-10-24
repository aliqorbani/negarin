<?php
/**
 * پیامک کوتاه «سفارش جدید» برای مدیران (جایگزین/مکمل پیام بله)
 *
 * فرمت: نام - موبایل مشتری - شماره سفارش - مبلغ - عنوان محصول
 *
 * تنظیمات در wp-config.php:
 * define('NEGARIN_ADMIN_SMS_NUMBERS', '09120000000,09130000000');
 * define('NEGARIN_MELLIPAYAMAK_SENDER', '...'); // قبلاً برای send_message لازم است
 */

use Negarin\Services\Sms\MelliPayamakGateway;

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('negarin_admin_sms_numbers')) {
    function negarin_admin_sms_numbers(): array
    {
        if (!defined('NEGARIN_ADMIN_SMS_NUMBERS')) {
            return [];
        }

        $numbers = [];
        foreach (explode(',', (string)NEGARIN_ADMIN_SMS_NUMBERS) as $raw) {
            $mobile = negarin_sms_notifier_normalize_mobile(trim($raw));
            if ($mobile !== '') {
                $numbers[] = $mobile;
            }
        }

        return array_values(array_unique($numbers));
    }
}

if (!function_exists('negarin_admin_sms_build_message')) {
    function negarin_admin_sms_build_message(WC_Order $order): string
    {
        $name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
        $mobile = $order->get_billing_phone();
        $total = html_entity_decode(wp_strip_all_tags(wc_price($order->get_total())), ENT_QUOTES, 'UTF-8');

        $items = array_values($order->get_items());
        $title = $items ? mb_strimwidth($items[0]->get_name(), 0, 30, '…', 'UTF-8') : '';
        if (count($items) > 1) {
            $title .= ' +' . (count($items) - 1);
        }

        return implode(' - ', array_filter([$name, $mobile, '#' . $order->get_order_number(), $total, $title]));
    }
}

if (!function_exists('negarin_admin_sms_on_success')) {
    function negarin_admin_sms_on_success($order_id, $order = null): void
    {
        $order = $order instanceof WC_Order ? $order : wc_get_order($order_id);
        if (!$order || $order->get_meta('_admin_sms_notified')) {
            return;
        }

        $numbers = negarin_admin_sms_numbers();
        if (!$numbers) {
            return;
        }

        $text = negarin_admin_sms_build_message($order);
        $gateway = new MelliPayamakGateway();
        $sent = 0;
        foreach ($numbers as $mobile) {
            if ($gateway->send_message($mobile, $text)) {
                $sent++;
            }
        }

        // اگر حداقل یک پیامک رفت، علامت می‌زنیم تا برای هر سفارش فقط یک بار ارسال شود
        if ($sent > 0) {
            $order->update_meta_data('_admin_sms_notified', 1);
            $order->save();
        } elseif (function_exists('wc_get_logger')) {
            wc_get_logger()->error('Admin SMS failed for order #' . $order->get_order_number(), ['source' => 'sms-admin-notifier']);
        }
    }
}

add_action('init', function () {
    add_action('woocommerce_order_status_processing', 'negarin_admin_sms_on_success', 20, 2);
    add_action('woocommerce_order_status_completed', 'negarin_admin_sms_on_success', 20, 2);
});