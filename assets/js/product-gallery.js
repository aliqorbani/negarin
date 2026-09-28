/**
 * Product gallery + lightbox (template-parts/components/product-gallery.php).
 *
 * - Mobile: native horizontal scroll-snap carousel with dot indicators.
 * - Desktop (md+): images stack full-width; clicking one opens the lightbox.
 * - Lightbox: one full image at a time, navigable by on-screen arrows,
 *   keyboard (Left/Right, Esc to close), and drag — Pointer Events, so a
 *   mouse drag on desktop and a finger swipe on mobile share one code path.
 *
 * RTL: this theme is always RTL, and `scrollLeft` sign conventions for RTL
 * scroll containers have differed between browser engines, so nothing here
 * reads or writes raw `scrollLeft`.
 *  - The active dot is tracked with IntersectionObserver, which only cares
 *    about which slide is visible, not about scroll offsets or direction.
 *  - Programmatic scrolling uses `scrollIntoView`, which browsers resolve
 *    correctly in either direction.
 *  - Keys/drag are mapped to the visual flow: in RTL the first image sits at
 *    the right and "next" moves toward the left, so ArrowLeft = next and a
 *    drag to the right = next (content follows the pointer). LTR is mirrored.
 *
 * Registered in app.js via Alpine.data('negarinProductGallery', ...).
 *
 * @param {number} total Number of gallery images.
 */
export function negarinProductGallery(total) {
    const DRAG_THRESHOLD = 60; // px of horizontal drag needed to change image
    const DESKTOP_QUERY = '(min-width: 768px)'; // Tailwind `md`

    return {
        total,
        active: 0,
        lightboxOpen: false,
        dragState: null,
        _wasDragged: false,
        _observer: null,

        init() {
            this.$nextTick(() => this._observeSlides());
        },

        destroy() {
            if (this._observer) {
                this._observer.disconnect();
                this._observer = null;
            }
        },

        /** Keeps `active` (the dots) in sync with the mobile carousel. */
        _observeSlides() {
            const track = this.$refs.track;
            if (!track || !('IntersectionObserver' in window)) return;

            this._observer = new IntersectionObserver(
                (entries) => {
                    // Desktop stacks the images, and while the lightbox is open it
                    // owns `active` — neither should be overwritten by the observer.
                    if (this.lightboxOpen || window.matchMedia(DESKTOP_QUERY).matches) return;

                    entries.forEach((entry) => {
                        if (entry.intersectionRatio < 0.6) return;
                        const index = Number(entry.target.dataset.index);
                        if (!Number.isNaN(index)) this.active = index;
                    });
                },
                { root: track, threshold: [0.6] }
            );

            Array.from(track.children).forEach((slide) => this._observer.observe(slide));
        },

        openLightbox(index) {
            this.active = index;
            this.lightboxOpen = true;
        },

        closeLightbox() {
            this.lightboxOpen = false;
            this.dragState = null;

            // On mobile, bring the inline carousel to the image the user ended on
            // so the dots and the visible slide match what they were just looking at.
            if (!window.matchMedia(DESKTOP_QUERY).matches) {
                const slide = this.$refs.track && this.$refs.track.children[this.active];
                if (slide) {
                    slide.scrollIntoView({ behavior: 'auto', inline: 'center', block: 'nearest' });
                }
            }
        },

        next() {
            if (this.total < 2) return;
            this.active = (this.active + 1) % this.total;
        },

        prev() {
            if (this.total < 2) return;
            this.active = (this.active - 1 + this.total) % this.total;
        },

        _isRtl() {
            return document.documentElement.dir === 'rtl';
        },

        handleKeydown(event) {
            if (!this.lightboxOpen) return;

            if (event.key === 'Escape') {
                this.closeLightbox();
                return;
            }

            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
            event.preventDefault();

            // The arrow points where the viewport travels: toward the left in RTL.
            const goesNext = this._isRtl() ? event.key === 'ArrowLeft' : event.key === 'ArrowRight';
            if (goesNext) {
                this.next();
            } else {
                this.prev();
            }
        },

        /** Tap on the empty area around the image closes; a drag never does. */
        onStageClick(event) {
            if (this._wasDragged) return;
            if (event.target === event.currentTarget) this.closeLightbox();
        },

        /** Live horizontal offset of the lightbox stage while dragging. */
        get dragDelta() {
            return this.dragState ? this.dragState.delta : 0;
        },

        onDragStart(event) {
            if (event.pointerType === 'mouse' && event.button !== 0) return;
            this._wasDragged = false;
            this.dragState = { startX: event.clientX, delta: 0 };
        },

        onDragMove(event) {
            if (!this.dragState) return;
            this.dragState.delta = event.clientX - this.dragState.startX;
        },

        onDragEnd() {
            if (!this.dragState) return;
            const { delta } = this.dragState;
            this.dragState = null;
            this._wasDragged = Math.abs(delta) > 5;

            if (Math.abs(delta) < DRAG_THRESHOLD) return;

            // Content follows the pointer: in RTL, dragging right pulls the next
            // image in from the left; in LTR, dragging left does.
            const draggedRight = delta > 0;
            const goesNext = this._isRtl() ? draggedRight : !draggedRight;
            if (goesNext) {
                this.next();
            } else {
                this.prev();
            }
        },
    };
}