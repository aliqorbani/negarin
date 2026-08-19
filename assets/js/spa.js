/**
 * Turbo Drive: makes internal navigation feel instant (fetches the next
 * page in the background and swaps just the <body>, instead of a full
 * browser reload) plus a connectivity banner for when the swap can't
 * happen at all — no internet, or our server not answering.
 *
 * Two things Turbo does NOT get to touch, on purpose:
 *
 * 1. Cart / checkout / My Account — excluded page-wide via the
 *    `turbo-visit-control` meta tag printed in inc/hooks/turbo.php. These
 *    carry WooCommerce session + payment-gateway state that a partial
 *    swap has no business interrupting.
 * 2. Anything WooCommerce already drives with its own AJAX (add-to-cart
 *    buttons, the cart quantity form, coupon/login/register forms) —
 *    tagged `data-turbo="false"` below so Turbo leaves the click/submit
 *    alone and WooCommerce's existing JS keeps working exactly as before.
 */

import * as Turbo from '@hotwired/turbo';

// Elements WooCommerce already drives with its own AJAX/JS. `data-turbo`
// is inherited by everything nested inside the element it's set on, so
// tagging the <form> or <a> itself is enough — no need to reach into
// every button/input.
const TURBO_EXCLUDED_SELECTORS = [
  '.ajax_add_to_cart',
  'form.cart',
  'form.woocommerce-cart-form',
  'form.woocommerce-checkout',
  '.woocommerce-form-coupon',
  '.woocommerce-form-login',
  '.woocommerce-form-register',
  'a.remove_from_cart_button',
  '.woocommerce-cart-form .remove',
];

function excludeWooCommerceInteractionsFromTurbo() {
  document.querySelectorAll(TURBO_EXCLUDED_SELECTORS.join(',')).forEach((el) => {
    el.setAttribute('data-turbo', 'false');
  });
}

// Run once for the initial load, then again after every Turbo navigation
// (the freshly-swapped page has its own set of these elements).
excludeWooCommerceInteractionsFromTurbo();
document.addEventListener('turbo:load', excludeWooCommerceInteractionsFromTurbo);

/**
 * Connectivity banner — "اتصال اینترنت خود را بررسی کنید" /
 * "سرور در دسترس نیست" — shown instead of a navigation just silently
 * doing nothing.
 */
const OFFLINE_MESSAGE = 'اتصال اینترنت خود را بررسی کنید.';
const BACK_ONLINE_MESSAGE = 'اتصال اینترنت برقرار شد.';
const SERVER_DOWN_MESSAGE = 'در حال حاضر سایت در دسترس نیست. کمی بعد دوباره تلاش کنید.';

let banner = null;
let hideTimer = null;

function ensureBanner() {
  if (banner) return banner;
  banner = document.createElement('div');
  banner.className = 'negarin-connectivity-banner';
  banner.setAttribute('role', 'alert');
  document.body.appendChild(banner);
  return banner;
}

function showBanner(message, { retry = false, autoHide = false } = {}) {
  const el = ensureBanner();
  clearTimeout(hideTimer);
  el.innerHTML = '';

  const text = document.createElement('span');
  text.textContent = message;
  el.appendChild(text);

  if (retry) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = 'تلاش مجدد';
    button.className = 'negarin-connectivity-banner__retry';
    button.addEventListener('click', () => window.location.reload());
    el.appendChild(button);
  }

  el.classList.add('is-visible');

  if (autoHide) {
    hideTimer = setTimeout(hideBanner, 3000);
  }
}

function hideBanner() {
  banner?.classList.remove('is-visible');
}

// The user's own connection dropping/returning.
window.addEventListener('offline', () => showBanner(OFFLINE_MESSAGE));
window.addEventListener('online', () => showBanner(BACK_ONLINE_MESSAGE, { autoHide: true }));

// Don't even attempt a visit if we already know we're offline — avoids a
// click that just hangs until the fetch eventually times out.
document.addEventListener('turbo:before-visit', (event) => {
  if (!navigator.onLine) {
    event.preventDefault();
    showBanner(OFFLINE_MESSAGE);
  }
});

// fetch() itself threw — no internet, DNS failure, our server not
// answering at all. This is the main case: "اینترنت کاربر مشکل داشت یا
// سرور ما مشکل داشت". Without this listener Turbo's fallback is a full
// page reload, which just reproduces the same failure with no explanation.
document.addEventListener('turbo:fetch-request-error', (event) => {
  event.preventDefault();
  showBanner(navigator.onLine ? SERVER_DOWN_MESSAGE : OFFLINE_MESSAGE, { retry: true });
});

// The server DID answer, just with a 5xx. Turbo will still render whatever
// HTML came back, which for a fatal PHP error may be blank or broken — so
// also surface the same banner as a non-blocking heads-up.
document.addEventListener('turbo:before-fetch-response', (event) => {
  if ((event.detail?.fetchResponse?.response?.status ?? 200) >= 500) {
    showBanner(SERVER_DOWN_MESSAGE, { retry: true, autoHide: true });
  }
});

// A navigation succeeded — clear any leftover banner from a previous
// failed attempt.
document.addEventListener('turbo:load', hideBanner);

export { Turbo };
