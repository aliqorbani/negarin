/**
 * Applies a `{ selector: htmlString }` fragments map — the same shape
 * WooCommerce's `woocommerce_add_to_cart_fragments` filter always returns —
 * to the DOM, then announces it so anything waiting on fresh markup (e.g.
 * toast.js re-scanning the notices fragment) can react.
 */
export function applyFragments(fragments) {
  if (!fragments) return;

  Object.entries(fragments).forEach(([selector, html]) => {
    document.querySelectorAll(selector).forEach((el) => {
      el.outerHTML = html;
    });
  });

  document.dispatchEvent(new CustomEvent('negarin:fragments-updated'));
}
