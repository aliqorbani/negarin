/**
 * Alpine component for template-parts/components/custom-order-modal.php.
 * Deliberately does NOT reimplement add-to-cart over fetch/AJAX — the form
 * posts directly to the product permalink with `add-to-cart`, which goes
 * through WooCommerce's normal (and normally-hookable) add-to-cart flow.
 * This component only manages UI state: preset/manual toggles are handled
 * per-field by their own small x-data blocks in the template.
 */
export function negarinCustomOrder() {
  return {
    loading: false,
    error: '',
    guestName: '',
    guestPhone: '',
    measurements: {},
  };
}
