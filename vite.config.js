import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [tailwindcss()],
  build: {
    manifest: true,
    outDir: 'public/build',
    rollupOptions: {
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/charts/forex-chart.js',
      ],
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
  },
});
