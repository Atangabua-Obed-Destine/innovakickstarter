// Playwright: end-to-end tests that drive a real browser through the real shop and the real API.
import { defineConfig } from '@playwright/test'

// Where your Laravel API repository is (next to this shop repository), and the port it runs on.
const API_DIR = process.env.API_DIR || '../nkwen-products-api'
const API_PORT = process.env.API_PORT || '8000'

export default defineConfig({
  testDir: './e2e',
  timeout: 60_000, // slow laptops are fine
  workers: 1, // one test at a time: they share one database
  use: {
    baseURL: 'http://localhost:5173',
    // Use the Chrome or Edge already on your computer, so you do not download another browser (about 100 MB+).
    channel: process.env.PW_CHANNEL || 'chrome',
    trace: 'retain-on-failure', // after a failure, "npx playwright show-trace" replays every step
  },
  // Playwright starts both servers for you (or uses them if they already run).
  webServer: [
    {
      command: `php artisan serve --port=${API_PORT}`,
      cwd: API_DIR,
      url: `http://127.0.0.1:${API_PORT}/api/products`,
      reuseExistingServer: true,
      timeout: 120_000,
    },
    {
      command: 'npm run dev -- --port=5173 --strictPort',
      url: 'http://localhost:5173',
      reuseExistingServer: true,
      timeout: 120_000,
      env: { VITE_API_URL: `http://127.0.0.1:${API_PORT}` },
    },
  ],
})
