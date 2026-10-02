/**
 * Alpine component for template-parts/components/size-select-modal.php.
 *
 * `sizeSelectOpen` / `sizeChartOpen` live on the ancestor x-data in
 * woocommerce/content-single-product.php, not here — this component only
 * owns the size grid + add-to-cart request. Crossing back out to the
 * ancestor (closing the modal after a successful add) is done with a
 * dispatched DOM event rather than `this.someAncestorProp = ...`, which
 * only reaches whichever scope actually defined that property when called
 * from *inside* a method body — dispatch avoids depending on that.
 */
import { applyFragments } from './fragments.js';


export function negarinSizeSelect({ productId, options }) {
  const sizeMeasurements = {
    '38': [
      { label: 'عرض سرشانه', value: 12.2 },
      { label: 'دور سینه', value: 88 },
      { label: 'دور بازو', value: 27.3 },
    ],
    '40': [
      { label: 'عرض سرشانه', value: 12.4 },
      { label: 'دور سینه', value: 92 },
      { label: 'دور بازو', value: 28.6 },
    ],
    '42': [
      { label: 'عرض سرشانه', value: 12.6 },
      { label: 'دور سینه', value: 96 },
      { label: 'دور بازو', value: 29.9 },
    ],
    '44': [
      { label: 'عرض سرشانه', value: 12.8 },
      { label: 'دور سینه', value: 100 },
      { label: 'دور بازو', value: 31.2 },
    ],
    '46': [
      { label: 'عرض سرشانه', value: 13 },
      { label: 'دور سینه', value: 104 },
      { label: 'دور بازو', value: 32.5 },
    ],
    '48': [
      { label: 'عرض سرشانه', value: 13.3 },
      { label: 'دور سینه', value: 110 },
      { label: 'دور بازو', value: 34.1 },
    ],
    '50': [
      { label: 'عرض سرشانه', value: 13.6 },
      { label: 'دور سینه', value: 116 },
      { label: 'دور بازو', value: 35.7 },
    ],
    '52': [
      { label: 'عرض سرشانه', value: 13.9 },
      { label: 'دور سینه', value: 122 },
      { label: 'دور بازو', value: 37.3 },
    ],
  };

  return {
    productId,
    options,
    selected: null,
    loading: false,
    error: '',

    get selectedOption() {
      return this.options.find((o) => o.slug === this.selected) || null;
    },

    get selectedMeasurements() {
      if (!this.selectedOption) return [];

      const size = String(this.selectedOption.slug).trim();
      // console.log('the size selected is : ' + size);
      // console.log('the selected is : ' + this.selectedOption.slug);
      console.log(sizeMeasurements[size]);
      return (sizeMeasurements[size] || []).slice(0, 3);
    },

    selectSize(option) {
      if (!option.in_stock) return;
      this.error = '';
      this.selected = option.slug;
      this.selectedMeasurements;
    },

    async addToCart() {
      const option = this.options.find((o) => o.slug === this.selected);
      if (!option || this.loading) return;

      this.loading = true;
      this.error = '';

      try {
        const res = await fetch(
            `${negarinData.restUrl}size-select/add-to-cart`,
            {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': negarinData.nonce,
              },
              body: JSON.stringify({
                product_id: this.productId,
                variation_id: option.variation_id,
              }),
            }
        );

        const data = await res.json();

        if (!res.ok) {
          throw new Error(data.message || 'خطایی رخ داد.');
        }

        applyFragments(data.fragments);
        window.negarinShowCartAddedModal();
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