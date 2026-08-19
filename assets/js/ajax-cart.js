/**
 * WooCommerce already ships `wc-add-to-cart.js` which handles the AJAX
 * request for elements with the `ajax_add_to_cart` class (enabled in
 * inc/hooks/woocommerce.php). This module only adds a small UX touch:
 * a brief "added" state on the button, since the default theme markup
 * doesn't include one out of the box.
 *
 * Bound on `document`, not `document.body`: Turbo Drive replaces the
 * <body> element on every navigation, which would silently kill a
 * listener attached to the old body reference.
 */
document.addEventListener('added_to_cart', (event) => {
  const button = event.target?.closest?.('.add_to_cart_button');
  if (!button) return;

  const original = button.textContent;
  button.textContent = 'اضافه شد ✓';
  button.classList.add('opacity-70');

  setTimeout(() => {
    button.textContent = original;
    button.classList.remove('opacity-70');
  }, 1500);
});
