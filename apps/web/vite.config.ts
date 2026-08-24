import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    host: true,
    hmr: {
      clientPort: 80,
    },
  },
  optimizeDeps: {
    // maplibre-gl loads its own worker as a separate chunk; Vite's
    // pre-bundling breaks that chunk's MIME type in dev, so it's excluded.
    exclude: ['maplibre-gl'],
  },
  test: {
    environment: 'jsdom',
    setupFiles: ['./src/test/setup.ts'],
  },
})
