<?php
/**
 * ارسال پیام سفارش موفق ووکامرس به بله (نسخه‌ی قالب، بدون نیاز به پلاگین)
 *
 * استفاده: در functions.php قالب اضافه کنید:
 * require_once get_template_directory() . '/inc/bale-notifier.php';
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ------------------------------------------------------------------
 * تنظیمات — اینجا را ویرایش کنید
 * ------------------------------------------------------------------ */
if (!function_exists('negarin_bale_configuration')) {

    function negarin_bale_configuration(): array
    {
        $config = [
            'enabled' => true,
            'token' => '1583627194:oUwvYwNd0EJVXHaTwLpuMQllniQAQE0fubE',
            'chat_ids' => ['310766046','938190012'],
            // Chat ID مدیر؛ برای چند نفر چند مورد اضافه کنید
            'timeout' => 10,           // ثانیه
            // وضعیت‌هایی که «سفارش موفق» حساب می‌شوند (بدون پیشوند wc-)
            'statuses' => ['processing', 'completed'],
        ];
        return apply_filters('negarin_bale_configuration', $config);
    }
}
/* ------------------------------------------------------------------
 * ارسال پیام به بله با curl
 * ------------------------------------------------------------------ */
if (!function_exists('negarin_bale_notifier_send')) {
    function negarin_bale_notifier_send($text): bool
    {
        $cfg = negarin_bale_configuration();
        if (empty($cfg['enabled']) || empty($cfg['token']) || empty($cfg['chat_ids'])) {
            return false;
        }

        $url = 'https://tapi.bale.ai/bot' . $cfg['token'] . '/sendMessage';
        $ok = true;

        foreach ($cfg['chat_ids'] as $chat_id) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode(
                    ['chat_id' => $chat_id, 'text' => $text],
                    JSON_UNESCAPED_UNICODE
                ),
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => (int)$cfg['timeout'],
            ]);

            $body = curl_exec($ch);
            $err = curl_error($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($body === false || $code !== 200) {
                $ok = false;
                if (function_exists('wc_get_logger')) {
                    wc_get_logger()->error(
                        'Bale send failed (chat ' . $chat_id . ', HTTP ' . $code . '): ' . ($err ?: $body),
                        ['source' => 'bale-notifier']
                    );
                }
            }
        }

        return $ok;
    }
}
/* ------------------------------------------------------------------
 * ساخت متن پیام
 * ------------------------------------------------------------------ */
if (!function_exists('negarin_bale_notifier_price')) {

    function negarin_bale_notifier_price($amount): string
    {
        return html_entity_decode(wp_strip_all_tags(wc_price($amount)), ENT_QUOTES, 'UTF-8');
    }
}

if(!function_exists('negarin_bale_notifier_build_message')) {
    function negarin_bale_notifier_build_message(WC_Order $order): string
    {
        $lines = [];
        $lines[] = '🛒 سفارش جدید موفق';
        $lines[] = '━━━━━━━━━━━━━━';
        $lines[] = 'شماره سفارش: #' . $order->get_order_number();
        $lines[] = 'تاریخ: ' . wp_date('Y/m/d H:i', $order->get_date_created()->getTimestamp());
        $lines[] = '';
        $lines[] = '👤 مشخصات مشتری';
        $lines[] = 'نام: ' . trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
        $lines[] = 'تلفن: ' . $order->get_billing_phone();
        if ($order->get_billing_email()) {
            $lines[] = 'ایمیل: ' . $order->get_billing_email();
        }

        $address = trim(implode('، ', array_filter([
            $order->get_shipping_state() ?: $order->get_billing_state(),
            $order->get_shipping_city() ?: $order->get_billing_city(),
            $order->get_shipping_address_1() ?: $order->get_billing_address_1(),
            $order->get_shipping_address_2() ?: $order->get_billing_address_2(),
        ])));
        $postcode = $order->get_shipping_postcode() ?: $order->get_billing_postcode();
        if ($address) {
            $lines[] = 'آدرس: ' . $address;
        }
        if ($postcode) {
            $lines[] = 'کدپستی: ' . $postcode;
        }

        $lines[] = '';
        $lines[] = '📦 اقلام سفارش';
        foreach ($order->get_items() as $item) {
            $lines[] = '• ' . $item->get_name() . ' × ' . $item->get_quantity()
                . ' = ' . negarin_bale_notifier_price($item->get_total() + $item->get_total_tax());
        }

//    $lines[] = '';
//    if ($order->get_shipping_method()) {
//        $lines[] = 'روش ارسال: ' . $order->get_shipping_method()
//            . ' (' . negarin_bale_notifier_price($order->get_shipping_total()) . ')';
//    }
        if ($order->get_total_discount() > 0) {
            $lines[] = 'تخفیف: ' . negarin_bale_notifier_price($order->get_total_discount());
        }
        $lines[] = 'روش پرداخت: ' . $order->get_payment_method_title();
        if ($order->get_transaction_id()) {
            $lines[] = 'کد پیگیری: ' . $order->get_transaction_id();
        }
        $lines[] = '💰 مبلغ کل: ' . negarin_bale_notifier_price($order->get_total());

        if ($order->get_customer_note()) {
            $lines[] = '';
            $lines[] = '📝 توضیحات مشتری: ' . $order->get_customer_note();
        }

        $lines[] = '';
        $lines[] = '🔗 ' . $order->get_edit_order_url();

        return implode("\n", $lines);
    }
}
/* ------------------------------------------------------------------
 * هوک‌ها — برای هر سفارش فقط یک بار ارسال می‌شود
 * ------------------------------------------------------------------ */
if(!function_exists('negarin_bale_notifier_on_success')) {
    function negarin_bale_notifier_on_success($order_id): void
    {
        $order = wc_get_order($order_id);
        if (!$order || $order->get_meta('_bale_notified')) {
            return;
        }

        if (negarin_bale_notifier_send(negarin_bale_notifier_build_message($order))) {
            $order->update_meta_data('_bale_notified', 1);
            $order->save();
        }
    }
}
add_action('init', function () {
    foreach (negarin_bale_configuration()['statuses'] as $status) {
        add_action('woocommerce_order_status_' . $status, 'negarin_bale_notifier_on_success', 20);
    }
});
