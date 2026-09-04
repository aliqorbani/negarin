/**
 * Alpine component for template-parts/components/custom-order-modal.php.
 * Requires a logged-in customer — the modal is never opened for a guest
 * in the first place (size-select-modal.php sends them to /my-account/
 * instead), but this still submits to a REST route that re-checks login
 * server-side (Services/CustomOrder.php::validate_submission()).
 */
import { applyFragments } from './fragments.js';

export function negarinCustomOrder({ productId }) {
  return {
    productId,
    loading: false,
    error: '',
    measurements: {
      chest: '',
      sleeve: '',
      shoulder: '',
      length: '',
    },

    async submit() {
      if (this.loading) return;
      this.loading = true;
      this.error = '';

      try {
        const res = await fetch(`${negarinData.restUrl}custom-order/add-to-cart`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': negarinData.nonce },
          body: JSON.stringify({ product_id: this.productId, measurements: this.measurements }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'خطایی رخ داد.');

        applyFragments(data.fragments);
        window.negarinToast(data.message, 'success');
        this.$dispatch('negarin:cart-added');
      } catch (e) {
        this.error = e.message;
        window.negarinToast(e.message, 'error');
      } finally {
        this.loading = false;
      }
    },
  };
}
