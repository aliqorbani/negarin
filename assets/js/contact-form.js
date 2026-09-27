/**
 * Alpine component for the contact form
 * (template-parts/components/contact-form.php, used by
 * templates/page-contact.php). Talks to inc/services/ContactForm.php
 * (submit) and inc/services/Captcha.php (self-hosted image captcha — no
 * reCAPTCHA/hCaptcha dependency).
 */
export function negarinContactForm() {
    return {
        fields: { name: '', phone: '', email: '', subject: '', message: '', website: '' },
        captchaToken: '',
        captchaImage: '',
        captchaAnswer: '',
        loadingCaptcha: false,
        submitting: false,
        fieldErrors: {},
        sent: false,

        init() {
            this.loadCaptcha();
        },

        async loadCaptcha() {
            this.loadingCaptcha = true;
            try {
                const res = await fetch(`${negarinData.restUrl}captcha`);
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'خطا در بارگذاری کد امنیتی.');

                this.captchaToken = data.token;
                this.captchaImage = data.image;
                this.captchaAnswer = '';
            } catch (e) {
                window.negarinToast(e.message, 'error');
            } finally {
                this.loadingCaptcha = false;
            }
        },

        async submit() {
            if (this.submitting) return;

            this.submitting = true;
            this.fieldErrors = {};

            try {
                const res = await fetch(`${negarinData.restUrl}contact/submit`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': negarinData.nonce },
                    body: JSON.stringify({
                        name: this.fields.name,
                        phone: this.fields.phone,
                        email: this.fields.email,
                        subject: this.fields.subject,
                        message: this.fields.message,
                        website: this.fields.website,
                        captcha_token: this.captchaToken,
                        captcha_answer: this.captchaAnswer,
                    }),
                });
                const data = await res.json();

                if (!res.ok) {
                    this.fieldErrors = data.data?.errors || {};
                    if (!Object.keys(this.fieldErrors).length) {
                        throw new Error(data.message || 'ارسال پیام با خطا مواجه شد.');
                    }
                    await this.loadCaptcha(); // Wrong/expired code is consumed either way — fetch a new one for the retry.
                    return;
                }

                this.sent = true;
            } catch (e) {
                window.negarinToast(e.message, 'error');
                await this.loadCaptcha();
            } finally {
                this.submitting = false;
            }
        },
    };
}