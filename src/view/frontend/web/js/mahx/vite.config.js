import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    lib: {
      entry: 'index.js',
      name: 'MahxCheckoutJS',
      fileName: 'checkout',
      formats: ['iife'], // for browser usage
    },
    minify: 'esbuild',
    outDir: 'build',
  },
});
