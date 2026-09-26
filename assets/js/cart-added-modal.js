/**
 * "به سبد خرید اضافه شد" success modal
 * (template-parts/components/cart-added-modal.php). Per the 2026-09
 * decision, a successful add-to-cart no longer confirms with a toast — it
 * opens this modal instead, with "ادامه خرید" (shop page) and "تکمیل خرید"
 * (cart page) actions. Errors are unaffected and still go through
 * toast.js.
 *
 * Two sources trigger it, both only on a *successful* add:
 *  1. Native: WooCommerce's own `added_to_cart` event, fired only on
 *     success by the shop-loop and single-product "ajax_add_to_cart"
 *     buttons (inc/hooks/woocommerce.php). toast.js still parses
 *     `#negarin-wc-notices` on this same event, but skips the plain
 *     success notice now that this modal covers it — any other notice
 *     riding along (stock/backorder info, say) still gets toasted.
 *  2. Custom: assets/js/size-select.js calls
 *     `window.negarinShowCartAddedModal()` directly after its own REST
 *     add-to-cart call succeeds (that endpoint never raises its own
 *     wc_add_notice(), so there's no notice-container path for it).
 *
 * Bound on `document`, not `document.body`, for the same reason as
 * assets/js/ajax-cart.js: Turbo Drive replaces <body> on every
 * navigation, which would silently kill a listener attached to the old
 * body reference.
 */
function initCartAddedModalGlobal() {
  window.negarinShowCartAddedModal = () => {
    window.dispatchEvent(new CustomEvent('negarin:cart-added-modal'));
  };
}

initCartAddedModalGlobal();

document.addEventListener('added_to_cart', () => {
  window.negarinShowCartAddedModal();
});
