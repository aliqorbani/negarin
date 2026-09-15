/**
 * AJAX-ifies the cart-page quantity stepper (template-parts/components/
 * quantity-stepper.php). The stepper's +/- buttons only touch a hidden
 * <input class="negarin-qty-input"> and dispatch a `change` event on it —
 * this used to just click the classic cart's hidden `update_cart` submit
 * button, which triggers a normal form POST and reloads the whole page on
 * every single change.
 *
 * A shopper can hit +/- (or type a number directly) several times before
 * settling on a value, so each change is debounced per cart-item-key: only
 * the value left standing after DEBOUNCE_MS actually goes out over the
 * wire. The confirm toast also uses a short duration (CART_TOAST_DURATION)
 * instead of the default 10s, so a follow-up change doesn't leave a pile of
 * "بروزرسانی شد" toasts stacked on screen — see Services/CartAjax.php for
 * the endpoint this calls.
 */
import { applyFragments } from './fragments.js';

const DEBOUNCE_MS = 500;
const CART_TOAST_DURATION = 3000;

const pendingTimers = new Map();

function cartItemKeyFromInput(input) {
  const match = input.name?.match(/^cart\[([^\]]+)\]\[qty\]$/);
  return match ? match[1] : null;
}

async function updateQuantity(cartItemKey, input, stepper) {
  const quantity = Math.max(1, parseInt(input.value, 10) || 1);
  stepper?.classList.add('loader');

  try {
    const res = await fetch(`${negarinData.restUrl}cart/update-item`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': negarinData.nonce },
      body: JSON.stringify({ cart_item_key: cartItemKey, quantity }),
    });
    const data = await res.json();

    if (!res.ok) {
      // Nothing changed server-side (bad cart_item_key / invalid quantity) — no fragments to apply.
      window.negarinToast(data.message || 'بروزرسانی سبد خرید با خطا مواجه شد.', 'error');
      return;
    }

    // The quantity was still committed even when success is false (e.g. it
    // exceeds available stock — WooCommerce flags that with a notice rather
    // than reverting it), so the sidebar/mini-cart must stay in sync either way.
    applyFragments(data.fragments);
    window.negarinToast(data.message, data.success ? 'success' : 'error', data.success ? CART_TOAST_DURATION : undefined);
  } catch (e) {
    window.negarinToast('ارتباط با سرور برقرار نشد.', 'error');
  } finally {
    stepper?.classList.remove('loader');
  }
}

document.addEventListener('change', (event) => {
  if (!event.target.classList?.contains('negarin-qty-input')) return;

  const input = event.target;
  const cartItemKey = cartItemKeyFromInput(input);
  if (!cartItemKey) return;

  const stepper = input.closest('.negarin-qty-stepper');

  clearTimeout(pendingTimers.get(cartItemKey));
  pendingTimers.set(
    cartItemKey,
    setTimeout(() => {
      pendingTimers.delete(cartItemKey);
      updateQuantity(cartItemKey, input, stepper);
    }, DEBOUNCE_MS)
  );
});
