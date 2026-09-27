/**
 * Alpine component for the footer "برای نگارین بنویسید" mini form
 * (template-parts/footer/site-footer.php). Submits to
 * inc/services/FooterMessage.php over REST instead of a real form
 * POST + redirect, so sending a message doesn't reload the page.
 */
export function negarinFooterMessage() {
    return {
        message: '',
        website: '',
        submitting: false,
        status: null, // 'sent' | 'empty' | null

        async submit() {
            if (this.submitting) return;

            if (!this.message.trim()) {
                this.status = 'empty';
                return;
            }

            this.submitting = true;
            this.status = null;

            try {
                const res = await fetch(`${negarinData.restUrl}footer-message`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': negarinData.nonce },
                    body: JSON.stringify({ message: this.message, website: this.website }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'ارسال پیام با خطا مواجه شد.');

                this.status = 'sent';
                this.message = '';
            } catch (e) {
                window.negarinToast(e.message, 'error');
            } finally {
                this.submitting = false;
            }
        },
    };
}