/**
 * Alpine component for the checkout form's two-step layout (address, then
 * payment) — powers woocommerce/checkout/form-checkout.php's `x-data`.
 *
 * IMPORTANT: this used to be written inline as `x-data="{ ... }"` directly
 * in the PHP template. Something in the WordPress render pipeline (almost
 * certainly wptexturize, judging by the symptoms: straight quotes turning
 * into curly ‘smart’ quotes and bare & turning into &#038;) was mangling
 * that raw JS whenever it went through `the_content` — likely because the
 * checkout page's content runs through the_content()'s full filter chain
 * to expand the [woocommerce_checkout] shortcode, and something re-applies
 * wptexturize afterwards. Moving the logic into an actual .js file sidesteps
 * the problem entirely rather than chasing down which filter does it: it's
 * no longer raw JS sitting inside an HTML attribute string for anything to
 * corrupt.
 */
export function negarinCheckoutForm() {
  return {
    step: 1,

    goToPayment() {
      // WooCommerce marks a required field by adding the
      // 'validate-required' class to the wrapping .form-row — not the
      // native HTML `required` attribute — so that's the signal to check
      // here, same as WooCommerce's own validation.js does.
      const rows = document.querySelectorAll('#customer_details .form-row.validate-required');
      let firstInvalid = null;
      rows.forEach((row) => {
        const field = row.querySelector('input, select, textarea');
        if (!field) return;
        const empty = !field.value || !String(field.value).trim();
        row.classList.toggle('negarin-field-error', empty);
        if (empty && !firstInvalid) firstInvalid = field;
      });
      if (firstInvalid) {
        firstInvalid.focus();
        firstInvalid.closest('.form-row').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }
      window.jQuery && jQuery(document.body).trigger('update_checkout');
      this.step = 2;
    },
  };
}

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

/**
 * Route checkout validation errors through the same toast pipeline as
 * every other WooCommerce notice (see inc/hooks/notices.php + toast.js)
 * instead of WooCommerce's own default `.woocommerce-error` alert box,
 * which checkout.js inserts directly into the DOM on AJAX failure — a
 * different code path than the hidden-container one the rest of the
 * site already uses, so it needs its own bridge here.
 */
document.addEventListener('DOMContentLoaded', () => {
  if (!window.jQuery) return;

  jQuery(document.body).on('checkout_error', () => {
    const noticeBox = document.querySelector('.woocommerce-NoticeGroup-checkout');
    const target = document.getElementById('negarin-wc-notices');
    if (!noticeBox || !target) return;

    target.innerHTML = noticeBox.innerHTML;
    noticeBox.remove();
    document.dispatchEvent(new CustomEvent('negarin:fragments-updated'));
  });
});

/**
 * Clears the red "required" highlight from goToPayment() (form-checkout.php)
 * as soon as the shopper actually fills the field in, instead of leaving it
 * red until the next click on "continue".
 */
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('customer_details');
  if (!container) return;

  const clearIfFilled = (e) => {
    const row = e.target.closest && e.target.closest('.form-row.negarin-field-error');
    if (row && e.target.value && String(e.target.value).trim()) {
      row.classList.remove('negarin-field-error');
    }
  };

  container.addEventListener('input', clearIfFilled);
  container.addEventListener('change', clearIfFilled);
});
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[name="payment_method"]').forEach((payment_method) => {
    payment_method.addEventListener('change', () => {
      console.log('payment method changed');
      console.log({payment: payment_method.value});
      jQuery(document.body).trigger('update_checkout');
    });
  });
});

/**
 * Figma puts the place-order button in the sidebar for both steps (step 1's
 * button is the Alpine "continue" one right above this slot; step 2's is
 * WooCommerce's own #place_order). WooCommerce renders #place_order inside
 * #order_review though, so we physically move it into the sidebar slot —
 * moving rather than cloning keeps checkout.js's delegated event handling
 * intact, since it listens on the form, not the button itself. Re-run on
 * every `updated_checkout` because WooCommerce replaces #order_review's
 * entire markup (place-order div included) on every totals/gateway change.
 */
function negarinRelocatePlaceOrder() {
  const slot = document.getElementById('negarin-place-order-slot');
  const placeOrderRow = document.querySelector('#order_review .place-order');
  if (!slot || !placeOrderRow) return;
  if (placeOrderRow.parentElement === slot) return; // already in place

  // WooCommerce re-renders a brand new .place-order div inside #order_review
  // on every `updated_checkout` refresh (shipping method, coupon, gateway
  // change, etc.) — the previously-moved one is now stale, so clear it out
  // before moving the fresh one in, or they'd pile up.
  slot.innerHTML = '';
  slot.appendChild(placeOrderRow);
}
document.addEventListener('DOMContentLoaded', negarinRelocatePlaceOrder);
document.addEventListener('DOMContentLoaded', () => {
  window.jQuery && jQuery(document.body).on('updated_checkout', negarinRelocatePlaceOrder);
});
