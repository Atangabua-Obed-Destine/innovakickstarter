/// <reference types="vitest/config" />
import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [react()],
  test: {
    // jsdom is a pretend browser that runs inside Node, so tests need no real browser.
    environment: 'jsdom',
    // Runs before every test file: adds matchers such as toBeInTheDocument().
    setupFiles: './src/setupTests.ts',
  },
})
