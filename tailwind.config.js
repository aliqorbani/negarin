/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './**/*.php',
    '!./node_modules/**',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        // Design tokens — the single source of truth for brand colors.
        // Update here, never hardcode hex values in templates.
        negarin: {
          ink: '#000000',   // near-black used for headings, header text, dark sections
          gray: '#333333',
          red: '#AA2B2B',
          cream: '#F4EFE9', // warm off-white background used behind text blocks
          gold: '#B08D57',  // accent used for eyebrow labels / hover states
          line: '#E5E0D8',  // hairline borders
        },
      },
      fontFamily: {
        // Swap these for the licensed brand fonts once supplied.
        serif: ['"Noto Serif"', 'serif'],
        sans: ['"Modam"','"Vazirmatn"', 'tahoma', 'system-ui', 'sans-serif'],
      },
      screens: {
        xs: '420px',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
