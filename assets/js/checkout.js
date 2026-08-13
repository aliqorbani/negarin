/**
 * WooCommerce triggers `checkout_error` on document.body (jQuery event)
 * when server-side validation fails on submit. If that happens while our
 * Alpine step is on "payment" (step 2), most validation errors are about
 * the address fields from step 1 — jump back so the shopper can see them.
 */
document.addEventListener('DOMContentLoaded', () => {
  if (!window.jQuery) return;

  jQuery(document.body).on('checkout_error', () => {
    const form = document.querySelector('form.checkout');
    if (!form || !window.Alpine) return;

    const data = window.Alpine.$data(form);
    if (data && data.step === 2) {
      data.step = 1;
    }

    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
});
