/**
 * The classic WooCommerce cart only recalculates totals when the
 * `update_cart` submit button is clicked. Our quantity-stepper component
 * only touches the underlying <input>, so this listens for its `change`
 * event and clicks that (visually hidden, see assets/css/app.css) button
 * for the shopper — no custom AJAX cart-math reimplementation needed.
 */
document.addEventListener('change', (event) => {
  if (!event.target.classList?.contains('negarin-qty-input')) return;

  const form = event.target.closest('form.woocommerce-cart-form');
  const updateButton = form?.querySelector('[name="update_cart"]');
  updateButton?.removeAttribute('disabled');
  updateButton?.click();
});
