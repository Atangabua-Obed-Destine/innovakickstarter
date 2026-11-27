import react from '@vitejs/plugin-react';
import { defineConfig } from 'vitest/config';

// Vite runs the app (npm run dev) and builds it (npm run build).
// The "test" part sets up Vitest (npm test) with jsdom, a pretend browser page.
export default defineConfig({
  plugins: [react()],
  test: {
    environment: 'jsdom',
    setupFiles: ['./src/test/setup.ts'],
  },
});
