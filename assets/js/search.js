/**
 * Alpine component powering template-parts/header/search-modal.php.
 * Talks to the REST route registered in inc/services/QuickSearch.php.
 *
 * `searchOpen` itself lives one scope up, on the <header x-data> element
 * (see site-header.php) — same boolean the old show/hide toggle used.
 */
export function negarinSearch() {
  return {
    query: '',
    results: [],
    loading: false,
    searched: false,
    error: '',
    _timer: null,

    init() {
      // Debounce as the user types.
      this.$watch('query', () => this.scheduleSearch());

      // Focus the input on open, and clear any stale results on close so
      // the modal always opens fresh next time.
      this.$watch('searchOpen', (open) => {
        if (open) {
          this.$nextTick(() => this.$refs.input?.focus());
        } else {
          clearTimeout(this._timer);
          this.query = '';
          this.results = [];
          this.searched = false;
          this.error = '';
        }
      });
    },

    scheduleSearch() {
      clearTimeout(this._timer);

      const term = this.query.trim();
      if (term.length < 2) {
        this.results = [];
        this.searched = false;
        this.loading = false;
        return;
      }

      this._timer = setTimeout(() => this.runSearch(term), 350);
    },

    async runSearch(term) {
      this.loading = true;
      this.error = '';
      try {
        const res = await fetch(`${negarinData.restUrl}search?q=${encodeURIComponent(term)}`);
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'خطا در جستجو');

        // Ignore stale responses if the query has changed since the request went out.
        if (term !== this.query.trim()) return;

        this.results = data.results || [];
        this.searched = true;
      } catch (e) {
        this.error = e.message;
        this.results = [];
      } finally {
        this.loading = false;
      }
    },
  };
}
