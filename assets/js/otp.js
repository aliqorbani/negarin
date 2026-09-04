/**
 * Alpine component powering template-parts/components/otp-login-form.php.
 * Talks to the REST routes registered in inc/services/OtpAuth.php.
 */
export function negarinOtp() {
  return {
    step: 'phone',
    phone: '',
    code: '',
    error: '',
    loading: false,
    cooldown: 0,
    _timer: null,
    // Preserved verbatim from the query string so an unrelated login (e.g.
    // arriving at /my-account/ directly) never picks up a stale redirect.
    redirectTo: new URLSearchParams(window.location.search).get('redirect_to') || '',

    async requestCode() {
      this.error = '';
      this.loading = true;
      try {
        const res = await fetch(`${negarinData.restUrl}otp/request`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': negarinData.nonce },
          body: JSON.stringify({ phone: this.phone }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'خطا در ارسال کد');
        this.step = 'code';
        this.startCooldown(data.resend_available_in || 60);
      } catch (e) {
        this.error = e.message;
      } finally {
        this.loading = false;
      }
    },

    async verifyCode() {
      this.error = '';
      this.loading = true;
      try {
        const res = await fetch(`${negarinData.restUrl}otp/verify`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': negarinData.nonce },
          body: JSON.stringify({ phone: this.phone, code: this.code, redirect_to: this.redirectTo }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'کد نامعتبر است');
        window.location.href = data.redirect || '/';
      } catch (e) {
        this.error = e.message;
      } finally {
        this.loading = false;
      }
    },

    async resendCode() {
      if (this.cooldown > 0) return;
      this.error = '';
      try {
        const res = await fetch(`${negarinData.restUrl}otp/resend`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': negarinData.nonce },
          body: JSON.stringify({ phone: this.phone }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'خطا در ارسال مجدد');
        this.startCooldown(data.resend_available_in || 60);
      } catch (e) {
        this.error = e.message;
      }
    },

    back() {
      clearInterval(this._timer);
      this.cooldown = 0;
      this.code = '';
      this.error = '';
      this.step = 'phone';
    },

    startCooldown(seconds) {
      this.cooldown = seconds;
      clearInterval(this._timer);
      this._timer = setInterval(() => {
        this.cooldown -= 1;
        if (this.cooldown <= 0) clearInterval(this._timer);
      }, 1000);
    },
  };
}
