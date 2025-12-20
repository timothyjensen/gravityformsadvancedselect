import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'assets',
    rollupOptions: {
      input: {
        'css/tom-select': './resources/css/tom-select.scss',
        'js/tom-select': './resources/js/tom-select.js',
        'js/tom-select-init-frontend': './resources/js/tom-select-init-frontend.js',
      },
      output: {
        assetFileNames: '[name].[ext]',
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
      }
    },
  }
});
