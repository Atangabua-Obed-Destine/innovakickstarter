# Starter: End-to-End Tests (Chapter 9, Full-stack)

Files for your React repository (`shop/`).

```
npm install --save-dev @playwright/test
npx playwright test            # headless: no window
npx playwright test --headed   # watch the browser click
npx playwright show-report     # the last results, in your browser
```

- `playwright.config.js` starts **both** servers for you: `php artisan serve` in your API folder (`API_DIR`, default `../nkwen-products-api`) and `npm run dev`.
- It uses the Chrome already on your computer (`channel: 'chrome'`). Edge: `PW_CHANNEL=msedge npx playwright test` (PowerShell: `$env:PW_CHANNEL="msedge"; npx playwright test`). No browser download is needed.
- Your API must be seeded: `php artisan migrate:fresh --seed` before the first run.

At the start: **1 passed, 3 failed** (`TODO: write this test`).
