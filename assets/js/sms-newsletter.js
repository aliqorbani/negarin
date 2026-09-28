/**
 * Alpine component for the SMS "notify me about new products" subscribe
 * form (template-parts/components/sms-newsletter-form.php), shared by the
 * [negarin_sms_newsletter] shortcode and the negarin/sms-newsletter block.
 * Talks to inc/services/SmsNewsletter.php over REST — this only records
 * the number; sending the actual SMS blast is a manual step done from
 * wp-admin, not triggered by anything here.
 */
export function negarinSmsNewsletter() {
    return {
        phone: '',
        website: '',
        submitting: false,
        fieldErrors: {},
        sent: false,
        alreadySubscribed: false,

        async submit() {
            if (this.submitting) return;

            this.submitting = true;
            this.fieldErrors = {};

            try {
                const res = await fetch(`${negarinData.restUrl}sms-newsletter/subscribe`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': negarinData.nonce },
                    body: JSON.stringify({ phone: this.phone, website: this.website }),
                });
                const data = await res.json();

                if (!res.ok) {
                    this.fieldErrors = data.data?.errors || {};
                    if (!Object.keys(this.fieldErrors).length) {
                        throw new Error(data.message || 'ثبت شماره با خطا مواجه شد.');
                    }
                    return;
                }

                this.alreadySubscribed = !!data.already;
                this.sent = true;
            } catch (e) {
                window.negarinToast(e.message, 'error');
            } finally {
                this.submitting = false;
            }
        },
    };
}
