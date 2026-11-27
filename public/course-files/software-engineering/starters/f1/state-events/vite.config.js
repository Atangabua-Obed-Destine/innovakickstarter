import react from '@vitejs/plugin-react';
import { defineConfig } from 'vitest/config';

// Vite runs the app (npm run dev) and builds it (npm run build).
// The "test" part sets up Vitest (npm test): jsdom is a pretend browser page,
// so tests can draw components without opening a real browser.
export default defineConfig({
  plugins: [react()],
  test: {
    environment: 'jsdom',
    setupFiles: ['./src/test/setup.js'],
  },
});
