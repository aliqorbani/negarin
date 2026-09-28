<?php
/**
 * SMS newsletter subscribe form — shared by the [negarin_sms_newsletter]
 * shortcode and the negarin/sms-newsletter block, so both always render
 * the exact same markup/behavior (inc/services/SmsNewsletter.php,
 * inc/services/SmsNewsletterBlock.php). Talks to
 * inc/services/SmsNewsletter.php over REST via
 * assets/js/sms-newsletter.js.
 *
 * Expects $title, $description, $button_text in scope.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div x-data="negarinSmsNewsletter()" class="negarin-sms-newsletter max-w-md">

    <template x-if="!sent">
        <form @submit.prevent="submit()" class="space-y-4 text-right">

            <input type="text" x-model="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

            <?php if ( ! empty( $title ) ) : ?>
                <h3 class="text-lg font-medium"><?php echo esc_html( $title ); ?></h3>
            <?php endif; ?>

            <?php if ( ! empty( $description ) ) : ?>
                <p class="text-sm opacity-70"><?php echo esc_html( $description ); ?></p>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input
                        type="tel"
                        x-model="phone"
                        dir="ltr"
                        placeholder="09xxxxxxxxx"
                        class="negarin-field-input"
                        :class="{ 'border-negarin-red': fieldErrors.phone }"
                    >
                    <p class="text-negarin-red text-sm mt-2" x-show="fieldErrors.phone" x-text="fieldErrors.phone"></p>
                </div>
                <button type="submit" class="btn btn--solid shrink-0" :disabled="submitting">
                    <span x-show="!submitting"><?php echo esc_html( $button_text ); ?></span>
                    <span x-show="submitting"><?php esc_html_e( 'در حال ثبت...', 'negarin' ); ?></span>
                </button>
            </div>
        </form>
    </template>

    <template x-if="sent">
        <div class="text-center py-6">
            <p
                class="bg-green-100 mb-0 p-4 px-0 text-base text-center"
                x-text="alreadySubscribed ? '<?php echo esc_js( __( 'شما قبلاً عضو خبرنامه شده‌اید.', 'negarin' ) ); ?>' : '<?php echo esc_js( __( 'ثبت شد! به محض انتشار محصولات جدید بهت خبر می‌دیم.', 'negarin' ) ); ?>'"
            ></p>
        </div>
    </template>
</div>
