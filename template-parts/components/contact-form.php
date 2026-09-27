<?php
/**
 * Contact form — used on the "تماس با ما" page template
 * (templates/page-contact.php). Self-hosted CAPTCHA
 * (inc/services/Captcha.php) + REST submit endpoint
 * (inc/services/ContactForm.php) via assets/js/contact-form.js — no
 * Google reCAPTCHA/hCaptcha dependency. Submissions are stored as
 * `negarin_contact_msg` posts, visible from the "تماس با ما" wp-admin menu.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div x-data="negarinContactForm()" class="max-w-xl mx-auto">

    <template x-if="!sent">
        <form @submit.prevent="submit()" class="space-y-6 text-right">

            <input type="text" x-model="fields.website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

            <div>
                <label class="block text-sm mb-2"><?php esc_html_e( 'نام', 'negarin' ); ?></label>
                <input type="text" x-model="fields.name" class="negarin-field-input" :class="{ 'border-negarin-red': fieldErrors.name }">
                <p class="text-negarin-red text-sm mt-2" x-show="fieldErrors.name" x-text="fieldErrors.name"></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-2"><?php esc_html_e( 'تلفن', 'negarin' ); ?></label>
                    <input type="tel" x-model="fields.phone" dir="ltr" class="negarin-field-input" :class="{ 'border-negarin-red': fieldErrors.phone }">
                    <p class="text-negarin-red text-sm mt-2" x-show="fieldErrors.phone" x-text="fieldErrors.phone"></p>
                </div>
                <div>
                    <label class="block text-sm mb-2"><?php esc_html_e( 'ایمیل (اختیاری)', 'negarin' ); ?></label>
                    <input type="email" x-model="fields.email" dir="ltr" class="negarin-field-input" :class="{ 'border-negarin-red': fieldErrors.email }">
                    <p class="text-negarin-red text-sm mt-2" x-show="fieldErrors.email" x-text="fieldErrors.email"></p>
                </div>
            </div>

            <div>
                <label class="block text-sm mb-2"><?php esc_html_e( 'موضوع (اختیاری)', 'negarin' ); ?></label>
                <input type="text" x-model="fields.subject" class="negarin-field-input">
            </div>

            <div>
                <label class="block text-sm mb-2"><?php esc_html_e( 'پیام', 'negarin' ); ?></label>
                <textarea
                    x-model="fields.message"
                    rows="5"
                    class="block w-full border bg-white px-4 py-3 text-base text-[#333] focus:outline-none focus:border-negarin-ink"
                    :class="fieldErrors.message ? 'border-negarin-red' : 'border-negarin-line'"
                ></textarea>
                <p class="text-negarin-red text-sm mt-2" x-show="fieldErrors.message" x-text="fieldErrors.message"></p>
            </div>

            <div>
                <label class="block text-sm mb-2"><?php esc_html_e( 'کد امنیتی تصویر', 'negarin' ); ?></label>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="shrink-0 border border-negarin-line bg-negarin-cream leading-[0]">
                        <img :src="captchaImage" width="170" height="60" alt="<?php esc_attr_e( 'کد امنیتی', 'negarin' ); ?>" class="block">
                    </div>
                    <button type="button" @click="loadCaptcha()" :disabled="loadingCaptcha" class="text-sm underline shrink-0">
                        <?php esc_html_e( 'تغییر کد', 'negarin' ); ?>
                    </button>
                    <input type="text" x-model="captchaAnswer" inputmode="numeric" dir="ltr" placeholder="<?php esc_attr_e( 'کد را وارد کنید', 'negarin' ); ?>" class="negarin-field-input flex-1 min-w-[140px]" :class="{ 'border-negarin-red': fieldErrors.captcha }">
                </div>
                <p class="text-negarin-red text-sm mt-2" x-show="fieldErrors.captcha" x-text="fieldErrors.captcha"></p>
            </div>

            <button type="submit" class="btn btn--solid w-full" :disabled="submitting">
                <span x-show="!submitting"><?php esc_html_e( 'ارسال پیام', 'negarin' ); ?></span>
                <span x-show="submitting"><?php esc_html_e( 'در حال ارسال...', 'negarin' ); ?></span>
            </button>
        </form>
    </template>

    <template x-if="sent">
        <div class="text-center py-10">
            <p class="text-lg mb-2"><?php esc_html_e( 'پیام شما ارسال شد.', 'negarin' ); ?></p>
            <p class="opacity-70 text-sm"><?php esc_html_e( 'به‌زودی با شما تماس می‌گیریم.', 'negarin' ); ?></p>
        </div>
    </template>
</div>