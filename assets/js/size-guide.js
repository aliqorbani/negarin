
export function negarinSizeGuide() {
    const sizes = [38, 40, 42, 44, 46, 48, 50, 52];

    const measurements = [
        {
            name: 'دور سینه',
            values: [88, 92, 96, 100, 104, 110, 116, 122],
        },
        {
            name: 'دور کمر',
            values: [67, 70, 74, 78, 82, 89, 96, 103],
        },
        {
            name: 'دور باسن',
            values: [94, 98, 102, 106, 110, 114, 120, 126],
        },
        {
            name: 'دور گردن',
            values: [35.3, 36.1, 36.9, 37.7, 38.5, 39.5, 40.5, 41.5],
        },
        {
            name: 'پشت یقه',
            values: [6.4, 6.6, 6.8, 7, 7.2, 7.5, 7.8, 8.12],
        },
        {
            name: 'بلندی کف حلقه',
            values: [19, 19.5, 20, 20.5, 21, 21.5, 22, 22.5],
        },
        {
            name: 'بلندی بالاتنه پشت',
            values: [40, 40, 40, 40, 40, 40, 40, 40],
        },
        {
            name: 'بلندی خط باسن',
            values: [59, 59.5, 60, 60.5, 61, 61.5, 62, 62.5],
        },
        {
            name: 'قد دامن',
            values: [60, 60.5, 61, 61.5, 62, 63, 64, 65],
        },
        {
            name: 'بلندی سینه',
            values: [24.8, 25.6, 26.4, 27.2, 28, 29.3, 30.6, 31.9],
        },
        {
            name: 'بلندی بالاتنه جلو',
            values: [43.8, 44.1, 44.4, 44.7, 45, 45.8, 46.6, 47.4],
        },
        {
            name: 'تیزه پشت',
            values: [16.5, 17, 17.5, 18, 18.5, 19.2, 19.9, 20.6],
        },
        {
            name: 'گشادی کف حلقه',
            values: [9.5, 10, 10.5, 11, 11.5, 12.2, 12.9, 13.6],
        },
        {
            name: 'کارور پیش',
            values: [18, 19, 20, 21, 22, 23.6, 25.2, 26.8],
        },
        {
            name: 'عرض سرشانه',
            values: [12.2, 12.4, 12.6, 12.8, 13, 13.3, 13.6, 13.9],
        },
        {
            name: 'قد آستین',
            values: [59, 59, 59, 59, 59, 59, 59, 59],
        },
        {
            name: 'دور بازو',
            values: [27.3, 28.6, 29.9, 31.2, 32.5, 34.1, 35.7, 37.3],
        },
        {
            name: 'دور مچ',
            values: [15.9, 16.3, 16.7, 17.1, 17.5, 17.9, 18.3, 18.7],
        },
        {
            name: 'قد کامل',
            values: [164, 164, 164, 164, 164, 164, 164, 164],
        },
    ];

    return {
        sizes,
        measurements,
        selectedSize: 40,

        get selectedMeasurements() {
            const index = this.sizes.indexOf(Number(this.selectedSize));

            if (index === -1) return [];

            return this.measurements.map((item) => ({
                name: item.name,
                value: item.values[index],
            }));
        },

        selectSize(size) {
            if (!this.sizes.includes(Number(size))) return;

            this.selectedSize = Number(size);
        },

        moveSize(event, currentIndex) {
            let nextIndex = currentIndex;

            switch (event.key) {
                case 'ArrowLeft':
                    nextIndex = (currentIndex + 1) % this.sizes.length;
                    break;

                case 'ArrowRight':
                    nextIndex =
                        (currentIndex - 1 + this.sizes.length) % this.sizes.length;
                    break;

                case 'Home':
                    nextIndex = 0;
                    break;

                case 'End':
                    nextIndex = this.sizes.length - 1;
                    break;

                default:
                    return;
            }

            event.preventDefault();

            const nextSize = this.sizes[nextIndex];
            this.selectSize(nextSize);

            this.$nextTick(() => {
                const root = this.$root;
                const nextTab = [...root.querySelectorAll('.sg-tab')].find(
                    (tab) => Number(tab.dataset.size) === nextSize
                );

                nextTab?.focus();
            });
        },
    };
}