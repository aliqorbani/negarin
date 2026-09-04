/**
 * Toast notifications (template-parts/components/toast-container.php).
 *
 * Two ways a message becomes a toast:
 *  1. Direct: assets/js/{size-select,custom-order}.js call
 *     `window.negarinToast(message, type)` themselves with the response
 *     from their own REST endpoint.
 *  2. Parsed: everything else that still calls WooCommerce's own
 *     wc_add_notice() the ordinary way (coupons, cart quantity updates,
 *     checkout validation, the native shop-loop AJAX add-to-cart button)
 *     ends up in the hidden `#negarin-wc-notices` container
 *     (inc/hooks/notices.php) instead of an inline page section.
 *     `parseNoticeContainer()` reads it, toasts each `<li>`, and empties
 *     it — run once on every page load/Turbo visit, and again whenever
 *     assets/js/fragments.js applies a fresh AJAX fragment.
 */

const DEFAULT_DURATION = 10000;

export function negarinToastStore() {
  return {
    items: [],
    _nextId: 1,

    push(message, type = 'info', duration = DEFAULT_DURATION) {
      if (!message) return;
      const id = this._nextId++;
      this.items.push({ id, message, type });
      window.setTimeout(() => this.remove(id), duration);
    },

    remove(id) {
      this.items = this.items.filter((item) => item.id !== id);
    },
  };
}

function textWithoutLinks(el) {
  const clone = el.cloneNode(true);
  clone.querySelectorAll('a').forEach((a) => a.remove());
  return clone.textContent.trim();
}

function parseNoticeContainer() {
  const container = document.getElementById('negarin-wc-notices');
  if (!container) return;

  // Error notices: WooCommerce wraps them all in one <ul class="woocommerce-error">, one <li> per message.
  container.querySelectorAll('ul.woocommerce-error li').forEach((li) => {
    window.negarinToast(textWithoutLinks(li), 'error');
  });

  // Success/info notices: each is its own standalone element — no <ul>/<li> involved.
  container.querySelectorAll('.woocommerce-message').forEach((el) => {
    window.negarinToast(textWithoutLinks(el), 'success');
  });
  container.querySelectorAll('.woocommerce-info').forEach((el) => {
    window.negarinToast(textWithoutLinks(el), 'info');
  });

  // Prevents the same notices from being toasted again if this container
  // is inspected before the next fragment refresh replaces it outright.
  container.innerHTML = '';
}

function initToastGlobal() {
  window.negarinToast = (message, type = 'info', duration = DEFAULT_DURATION) => {
    window.dispatchEvent(new CustomEvent('negarin:toast', { detail: { message, type, duration } }));
  };
}

initToastGlobal();

document.addEventListener('DOMContentLoaded', parseNoticeContainer);
document.addEventListener('turbo:load', parseNoticeContainer);
document.addEventListener('negarin:fragments-updated', parseNoticeContainer);
document.addEventListener('added_to_cart', parseNoticeContainer);
