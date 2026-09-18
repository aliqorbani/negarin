<?php
/**
 * 🤖 سئو هوشمند با AI – نسخه نهایی (اصلاح‌شده)
 * – توضیحات ۴۰۰-۵۰۰ کلمه، خلاصه و تیتروار (H3 + بولت)
 * – پر کردن خودکار ویرایشگر + عنوان سئو + متا بدون افزونه
 */

// ─── متاباکس + دکمه ───
add_action('add_meta_boxes', function () {
    add_meta_box('ai_seo_box', '🤖 سئو هوشمند (AI)', 'ai_seo_box_html', 'product', 'side', 'high');
});
function ai_seo_box_html($post)
{
    wp_nonce_field('ai_seo_nonce', 'ai_seo_nonce_field');
    echo '<button type="button" id="ai-generate-btn" class="button button-primary" style="width:100%">🤖 تولید عنوان، متا و توضیحات</button>';

    $t = get_post_meta($post->ID, '_ai_seo_title', true);
    $m = get_post_meta($post->ID, '_ai_meta_desc', true);
    if ($t || $m) {
        echo '<div style="margin-top:10px;background:#f6f7f7;padding:8px;border-radius:6px;font-size:12px;line-height:1.8">';
        echo '<strong>📌 ذخیره‌شده‌ها:</strong><br>🏷️ عنوان: ' . esc_html($t) . '<br>📄 متا: ' . esc_html($m);
        echo '</div>';
    }
    echo '<div id="ai-result" style="margin-top:10px;font-size:12px;line-height:1.8"></div>';
}

// ─── جاوااسکریپت + پر کردن خودکار ویرایشگر ───
add_action('admin_footer', function () {
    global $post;
    if (!isset($post) || $post->post_type !== 'product') return;
    ?>
    <script>
        function fillDescEditor(desc) {
            try {
                if (typeof wp !== 'undefined' && wp.data && wp.data.select && wp.data.select('core/editor') && wp.data.select('core/editor').getCurrentPost) {
                    wp.data.dispatch('core/editor').editPost({content: desc});
                    return;
                }
            } catch (e) {
            }
            if (typeof tinymce !== 'undefined' && tinymce.get && tinymce.get('content')) {
                tinymce.get('content').setContent(desc);
            }
            if (jQuery('#content').length) {
                jQuery('#content').val(desc);
            }
        }

        jQuery(function ($) {
            $('#ai-generate-btn').on('click', function () {
                var btn = $(this), box = $('#ai-result');
                btn.prop('disabled', true).text('⏳ در حال تولید…');
                $.post(ajaxurl, {
                    action: 'ai_generate_seo',
                    nonce: $('#ai_seo_nonce_field').val(),
                    post_id: <?php echo (int)get_the_ID(); ?>
                }, function (res) {
                    btn.prop('disabled', false).text('🤖 تولید عنوان، متا و توضیحات');
                    if (res.success) {
                        fillDescEditor(res.data.desc_raw);
                        box.html(
                            '<div style="background:#e8f5e9;padding:8px;border-radius:6px">' +
                            '<strong>عنوان سئو:</strong> ' + res.data.seo_title +
                            '<br><strong>متا:</strong> ' + res.data.meta_desc +
                            '<br>✅ ذخیره شد + ویرایشگر پر شد (' + res.data.words + ' کلمه) | 🤖 ' + res.data.model +
                            '</div>'
                        );
                    } else {
                        box.html('<div style="color:#b00">❌ ' + res.data + '</div>');
                    }
                }).fail(function () {
                    btn.prop('disabled', false).text('🤖 تولید عنوان، متا و توضیحات');
                    box.html('<div style="color:#b00">❌ خطا در ارتباط</div>');
                });
            });
        });
    </script>
    <?php
});

// ─── هندلر AJAX ───
add_action('wp_ajax_ai_generate_seo', 'ai_generate_seo_handler');
function ai_generate_seo_handler()
{
    check_ajax_referer('ai_seo_nonce', 'nonce');
    if (!current_user_can('manage_woocommerce')) wp_send_json_error('دسترسی غیرمجاز.');
    if (!defined('AI_API_KEY') || !defined('AI_API_URL')) wp_send_json_error('تنظیمات AI در wp-config کامل نیست.');

    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $product = wc_get_product($post_id);
    if (!$product) wp_send_json_error('محصول پیدا نشد.');

    $title = trim($product->get_name());
    if ($title === '') wp_send_json_error('عنوان محصول خالیه؛ اول محصول رو ذخیره کن.');

// ✅ پرامپت آپدیت‌شده: توضیحات ۴۰۰-۵۰۰ کلمه، خلاصه و تیتروار
    $prompt = 'برای محصولی با عنوان «' . $title . '» این ۳ مورد رو بساز و فقط JSON معتبر برگردون:

1) seo_title: عنوان سئو (حداکثر ۶۰ کاراکتر) – شامل نام محصول + کلمه قدرتمند (خرید، بهترین، اصل، فوری)

2) meta_desc: متا دیسکریپشن (حداکثر ۱۵۵ کاراکتر) – ترغیب به کلیک + CTA + مزیت کلیدی

3) product_desc: توضیحات کامل محصول، بین ۴۰۰ تا ۵۰۰ کلمه، به صورت خلاصه و تیتروار.
    حتماً با تگ‌های HTML بنویس و دقیقاً این ساختار رو رعایت کن:
<p>یک پاراگراف معرفی کوتاه و جذاب (۲-۳ جمله)</p>
<h3>✨ ویژگی‌های کلیدی</h3>
<ul><li>مورد ۱</li><li>مورد ۲</li><li>مورد ۳</li><li>مورد ۴</li></ul>
<h3>💰 مزایای خرید</h3>
<ul><li>مزیت ۱</li><li>مزیت ۲</li><li>مزیت ۳</li></ul>
<h3>📋 مشخصات و کاربرد</h3>
<ul><li>کاربرد ۱</li><li>کاربرد ۲</li><li>کاربرد ۳</li></ul>
<h3>🚚 خرید و ارسال</h3>
<p>پاراگراف پایانی با CTA و ترغیب به خرید (۲-۳ جمله)</p>
    – جملات کوتاه و خلاصه باشن، نه متن طولانی
– لحن: حرفه‌ای، صمیمی، فروشندی + ایموجی مناسب
– مجموع کلمات بین ۴۰۰ تا ۵۰۰ باشه

کلیدهای JSON: seo_title, meta_desc, product_desc';

    $models = array(
            'google/gemma-4-31b:free',
            'google/gemma-4-26b-a4b:free',
            'poolside/laguna-s-2.1:free',
            'poolside/laguna-xs-2.1:free',
            'thinkingmachines/inkling:free',
            'cohere/north-mini-code:free',
            'z-ai/glm-5.2:free',
            'nvidia/nemotron-3-ultra:free',
            'nvidia/nemotron-3-super:free',
            'nvidia/nemotron-3-nano-omni:free',
            'minimax/minimax-m3:free',
            'minimax/minimax-m2.7:free',
    );
    if (defined('AI_MODEL') && !in_array(AI_MODEL, $models)) array_unshift($models, AI_MODEL);
    $models = array_values(array_unique($models));

    $last_error = '';

    foreach ($models as $model) {
        $response = wp_remote_post(AI_API_URL, array(
                'headers' => array('Authorization' => 'Bearer ' . AI_API_KEY, 'Content-Type' => 'application/json'),
                'body' => json_encode(array(
                        'model' => $model,
                        'messages' => array(
                                array('role' => 'system', 'content' => 'تو متخصص سئو و کپی‌رایتر فروشگاهی هستی. فقط JSON معتبر برگردون.'),
                                array('role' => 'user', 'content' => $prompt),
                        ),
                )),
                'timeout' => 90,
        ));

        if (is_wp_error($response)) {
            $last_error = 'اتصال: ' . $response->get_error_message();
            continue;
        }

        $code = (int)wp_remote_retrieve_response_code($response);
        $raw = wp_remote_retrieve_body($response);
        $body = json_decode($raw, true);

        if ($code !== 200 || isset($body['error']['message'])) {
            $last_error = $model . ' → ' . ($body['error']['message'] ?? ('HTTP ' . $code));
            continue;
        }

        $content = $body['choices'][0]['message']['content'] ?? '';
        // اصلاح شد: بک‌تیک واقعی به‌جای کوتیشن فانتزی، برای حذف فنس ```json احتمالی
        $content = preg_replace('/```(?:json)?/i', '', $content);
        $ai = json_decode($content, true);
        if (!is_array($ai) && preg_match('/{.*}/s', $content, $m)) $ai = json_decode($m[0], true);

        if (!$ai || empty($ai['seo_title'])) {
            $last_error = $model . ' → JSON نامعتبر';
            continue;
        }

        $seo_title = sanitize_text_field($ai['seo_title']);
        $meta_desc = sanitize_text_field($ai['meta_desc']);
        $desc = wp_kses_post($ai['product_desc']);

// شمارش کلمات برای نمایش
        $words = str_word_count(wp_strip_all_tags($desc));
// برای متن فارسی، شمارش دقیق‌تر با explode — اصلاح شد: \s+ به‌جای s+
        $fa_words = count(preg_split('/\s+/', trim(wp_strip_all_tags($desc)), -1, PREG_SPLIT_NO_EMPTY));
        $words = max($words, $fa_words);

// ذخیره رسمی ووکامرس
        $product->set_description($desc);
        $product->save();
        wp_update_post(array('ID' => $post_id, 'post_content' => $desc));
        clean_post_cache($post_id);

        update_post_meta($post_id, '_ai_seo_title', $seo_title);
        update_post_meta($post_id, '_ai_meta_desc', $meta_desc);
        update_post_meta($post_id, '_ai_product_desc', $desc);

        if (defined('WPSEO_VERSION')) {
            update_post_meta($post_id, '_yoast_wpseo_title', $seo_title);
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_desc);
        }
        if (defined('RANK_MATH_VERSION')) {
            update_post_meta($post_id, '_rank_math_title', $seo_title);
            update_post_meta($post_id, '_rank_math_description', $meta_desc);
        }

        wp_send_json_success(array(
                'seo_title' => esc_html($seo_title),
                'meta_desc' => esc_html($meta_desc),
                'desc_raw' => $desc,
                'words' => (int)$words,
                'model' => esc_html($model),
        ));
    }

    wp_send_json_error('همه مدل‌ها خطا دادن. آخرین خطا: ' . $last_error);
}

// ─── عنوان سئو + متا در صفحه (بدون افزونه) ───
add_filter('pre_get_document_title', function ($title) {
    if (function_exists('is_product') && is_product()
            && !defined('WPSEO_VERSION') && !defined('RANK_MATH_VERSION')) {
        $t = get_post_meta(get_the_ID(), '_ai_seo_title', true);
        if ($t) return $t;
    }
    return $title;
}, 999);

add_action('wp_head', function () {
    if (function_exists('is_product') && is_product()
            && !defined('WPSEO_VERSION') && !defined('RANK_MATH_VERSION')) {
        $m = get_post_meta(get_the_ID(), '_ai_meta_desc', true);
        if ($m) echo '<meta name="description" content="' . esc_attr($m) . '">' . "\n";
    }
}, 1);

// ✅ تولید خودکار سئو هنگام ذخیره (پیشنویس / در انتظار / منتشر) — فقط یک‌بار
add_action('save_post_product', 'ai_auto_seo_on_save', 20, 1);
function ai_auto_seo_on_save($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_products')) return;
    if (get_post_meta($post_id, '_ai_seo_title', true)) return; // قبلاً ساخته شده، بازنویسی نکن
    $title = get_the_title($post_id);
    if ($title === '') return;
    if (!defined('AI_API_KEY') || !defined('AI_API_URL')) return;

    $model = defined('AI_MODEL') ? AI_MODEL : 'google/gemma-4-31b:free';
    $prompt = 'برای محصول «' . $title . '» فقط JSON معتبر برگردون با کلیدهای: seo_title (حداکثر ۶۰ کاراکتر)، meta_desc (حداکثر ۱۵۵ کاراکتر)، product_desc (توضیحات ۴۰۰-۵۰۰ کلمه، تیتروار با تگ HTML).';

    $response = wp_remote_post(AI_API_URL, array(
            'headers' => array('Authorization' => 'Bearer ' . AI_API_KEY, 'Content-Type' => 'application/json'),
            'body' => json_encode(array(
                    'model' => $model,
                    'messages' => array(
                            array('role' => 'system', 'content' => 'فقط JSON معتبر برگردون.'),
                            array('role' => 'user', 'content' => $prompt),
                    ),
            )),
            'timeout' => 90,
    ));
    if (is_wp_error($response)) return;

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $content = $body['choices'][0]['message']['content'] ?? '';
    // اصلاح شد: بک‌تیک واقعی به‌جای کوتیشن فانتزی
    $content = preg_replace('/```(?:json)?/i', '', $content);
    $ai = json_decode($content, true);
    if (!is_array($ai) && preg_match('/{.*}/s', $content, $m)) $ai = json_decode($m[0], true);
    if (!$ai || empty($ai['seo_title'])) return;

    $desc = wp_kses_post($ai['product_desc']);

    update_post_meta($post_id, '_ai_seo_title', sanitize_text_field($ai['seo_title']));
    update_post_meta($post_id, '_ai_meta_desc', sanitize_text_field($ai['meta_desc']));
    update_post_meta($post_id, '_ai_product_desc', $desc);
    wp_update_post(array('ID' => $post_id, 'post_content' => $desc));
}