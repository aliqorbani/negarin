/**
 * .negarin-header is `position: sticky; top: 0`. Anything else that also
 * wants to stick below it (the desktop account sidebar in
 * woocommerce/myaccount/my-account.php) can't just use `top: 0` too — both
 * would stick flush to the viewport top and overlap. It needs `top:` set
 * to the header's real height instead.
 *
 * That height isn't a safe constant to hardcode: it depends on whichever
 * logo image is set in Theme Options, which has no fixed height, only a
 * max-width (see .negarin-logo .logo-image in assets/css/app.css). So we
 * measure it and expose it as a CSS variable everything else can read.
 *
 * My Account pages are excluded from Turbo Drive (see spa.js), so in
 * practice this only ever needs to run on a normal full page load — the
 * turbo:load listener is just cheap insurance in case that ever changes.
 */

function setNegarinHeaderOffset() {
    const header = document.querySelector('.negarin-header');
    if (!header) return;
    document.documentElement.style.setProperty('--negarin-header-h', `${header.offsetHeight}px`);
}

setNegarinHeaderOffset();
document.addEventListener('DOMContentLoaded', setNegarinHeaderOffset);
document.addEventListener('turbo:load', setNegarinHeaderOffset);
window.addEventListener('resize', setNegarinHeaderOffset);