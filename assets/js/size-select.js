/**
 * Alpine component for template-parts/components/size-select-modal.php.
 *
 * `sizeSelectOpen` / `sizeChartOpen` / `customOrderOpen` all live on the
 * ancestor x-data in woocommerce/content-single-product.php, not here —
 * this component only owns the size grid + add-to-cart request. Crossing
 * back out to the ancestor (closing everything after a successful add, or
 * opening the custom-order modal) is done with a dispatched DOM event
 * rather than `this.someAncestorProp = ...`, which only reaches whichever
 * scope actually defined that property when called from *inside* a
 * method body — dispatch avoids depending on that.
 */
import { applyFragments } from './fragments.js';

export function negarinSizeSelect({ productId, options, isLoggedIn, loginRedirectUrl }) {
  return {
    productId,
    options,
    isLoggedIn,
    loginRedirectUrl,
    selected: null,
    loading: false,
    error: '',

    selectSize(option) {
      if (!option.in_stock) return;
      this.error = '';
      this.selected = option.slug;
    },

    goToCustomOrder() {
      if (!this.isLoggedIn) {
        window.location.href = this.loginRedirectUrl;
        return;
      }
      this.$dispatch('negarin:open-custom-order');
    },

    async addToCart() {
      const option = this.options.find((o) => o.slug === this.selected);
      if (!option || this.loading) return;

      this.loading = true;
      this.error = '';

      try {
        const res = await fetch(`${negarinData.restUrl}size-select/add-to-cart`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': negarinData.nonce },
          body: JSON.stringify({ product_id: this.productId, variation_id: option.variation_id }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'خطایی رخ داد.');

        applyFragments(data.fragments);
        window.negarinToast(data.message, 'success');
        this.selected = null;
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
