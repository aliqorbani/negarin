import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  base: '/wp-content/themes/negarin/assets/build/',
  build: {
    manifest: true,
    outDir: 'assets/build',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        app: resolve(__dirname, 'assets/js/app.js'),
      },
    },
  },
  server: {
    origin: 'http://localhost:5173',
    cors: true,
  },
});
