<?php
/**
 * ارسال پیامک به مشتری پس از تکمیل سفارش ووکامرس
 *
 * استفاده: در functions.php قالب اضافه کنید:
 * require_once get_template_directory() . '/inc/sms-notifier.php';
 */

use Negarin\Services\Sms\MelliPayamakGateway;
use Negarin\Services\Sms\MelliPayamakConfig;

if (!defined('ABSPATH')) {
    exit;
}

/* ------------------------------------------------------------------
 * تنظیمات — اینجا را ویرایش کنید
 * ------------------------------------------------------------------ */
if (!function_exists('negarin_sms_configuration')) {

    function negarin_sms_configuration(): array
    {
        $config = [
            'enabled' => true,
            'timeout' => 10,           // ثانیه
            // اطلاعات پنل پیامک — هر چه سرویس‌دهنده‌تان نیاز دارد اینجا بگذارید
            'api_url' => 'https://example.com/api/send',
            'api_key' => 'PUT_YOUR_API_KEY_HERE',
            'sender' => '3000xxxx',    // شماره خط ارسال‌کننده
            // وضعیت‌هایی که پیامک برایشان ارسال می‌شود (بدون پیشوند wc-)
            'statuses' => ['completed'],
            // متغیرها: {name} {order_number} {total} {site_name}
            'message' => "{name} عزیز، سفارش شماره {order_number} شما تکمیل شد و آماده ارسال است. با تشکر از خرید شما - {site_name}",
        ];

        return apply_filters('negarin_sms_configuration', $config);
    }
}

/* ------------------------------------------------------------------
 * ارسال پیامک با curl — این تابع را با API پنل خودتان جایگزین کنید
 * ورودی: شماره موبایل (09xxxxxxxxx) و متن پیامک
 * خروجی: true در صورت موفقیت، در غیر این صورت false
 * ------------------------------------------------------------------ */
if (!function_exists('negarin_sms_notifier_send')) {
    function negarin_sms_notifier_send(string $mobile, string $text): bool
    {
        $cfg = negarin_sms_configuration();
        if (empty($cfg['enabled']) || empty($cfg['api_url'])) {
            return false;
        }

        $ch = curl_init($cfg['api_url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $cfg['api_key'],
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'from' => $cfg['sender'],
                'to' => $mobile,
                'message' => $text,
            ], JSON_UNESCAPED_UNICODE),
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => (int)$cfg['timeout'],
        ]);

        $body = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $code < 200 || $code >= 300) {
            if (function_exists('wc_get_logger')) {
                wc_get_logger()->error(
                    'SMS send failed (' . $mobile . ', HTTP ' . $code . '): ' . ($err ?: $body),
                    ['source' => 'sms-notifier']
                );
            }
            return false;
        }

        return true;
    }
}

/* ------------------------------------------------------------------
 * نرمال‌سازی شماره موبایل ایرانی به فرم 09xxxxxxxxx
 * ------------------------------------------------------------------ */
if (!function_exists('negarin_sms_notifier_normalize_mobile')) {
    function negarin_sms_notifier_normalize_mobile($mobile): string
    {
        // تبدیل اعداد فارسی و عربی به انگلیسی
        $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $ar = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $mobile = str_replace($ar, range(0, 9), str_replace($fa, range(0, 9), (string)$mobile));
        $mobile = preg_replace('/\D+/', '', $mobile);

        if (strpos($mobile, '0098') === 0) {
            $mobile = '0' . substr($mobile, 4);
        } elseif (strpos($mobile, '98') === 0) {
            $mobile = '0' . substr($mobile, 2);
        } elseif (strlen($mobile) === 10 && $mobile[0] === '9') {
            $mobile = '0' . $mobile;
        }

        return preg_match('/^09\d{9}$/', $mobile) ? $mobile : '';
    }
}

/* ------------------------------------------------------------------
 * ساخت متن پیامک
 * ------------------------------------------------------------------ */
if (!function_exists('negarin_sms_notifier_build_message')) {
    function negarin_sms_notifier_build_message(WC_Order $order): string
    {
        $cfg = negarin_sms_configuration();

        return strtr($cfg['message'], [
            '{name}' => $order->get_billing_first_name() ?: 'مشتری',
            '{order_number}' => $order->get_order_number(),
            '{total}' => html_entity_decode(wp_strip_all_tags(wc_price($order->get_total())), ENT_QUOTES, 'UTF-8'),
            '{site_name}' => get_bloginfo('name'),
        ]);
    }
}

/* ------------------------------------------------------------------
 * هوک‌ها — برای هر سفارش فقط یک بار ارسال می‌شود
 * ------------------------------------------------------------------ */
if (!function_exists('negarin_sms_notifier_on_status')) {
    function negarin_sms_notifier_on_status(int $order_id,$order ): void
    {
        $order = $order instanceof WC_Order ? $order : wc_get_order( $order_id );
        if (!$order || $order->get_meta('_sms_notified')) {
            return;
        }

        $mobile = get_user_meta($order->get_customer_id(), 'negarin_phone', true) ?: $order->get_billing_phone();

//        $mobile = negarin_sms_notifier_normalize_mobile();
        if ($mobile === '') {
            $order->add_order_note('پیامک ارسال نشد: شماره موبایل معتبر نیست.');
            return;
        }
        $sms_gateway = new MelliPayamakGateway( new MelliPayamakConfig() );

        $sms_send = $sms_gateway->send_order_confirmed($mobile,[$order->get_billing_first_name(),(string) $order_id]);

        if ($sms_send) {
            $order->update_meta_data('_sms_notified', 1);
            $order->add_order_note('پیامک برای ' . $mobile . ' ارسال شد.');
            $order->save();
        } else {
            $order->add_order_note('ارسال پیامک ناموفق بود (جزئیات در لاگ ووکامرس).');
        }
    }
}
//add_action('init', function () {
//    foreach (negarin_sms_configuration()['statuses'] as $status) {
//        add_action('woocommerce_order_status_' . $status, 'negarin_sms_notifier_on_status', 20);
//    }
//});

add_action('init',function(){
    add_action('woocommerce_order_status_processing','negarin_sms_notifier_on_status',20,2);
    add_action('woocommerce_order_status_completed','negarin_sms_notifier_on_status',20,2);
});