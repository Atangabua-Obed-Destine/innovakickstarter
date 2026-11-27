# Starter: Connecting a React App to Your Laravel API (Chapter 9, Full-stack)

| File | Goes in | What it is |
|---|---|---|
| `api/tests/Feature/CorsTest.php` | your Laravel repo | 3 tests: only your shop may read the API from a browser. |
| `shop/src/api.js` | your React repo | Every call to the API. 3 TODOs. |
| `shop/src/api.test.js` | your React repo | 6 Vitest tests for `api.js`. |
| `shop/.env.development` | your React repo | The API address on your laptop. |
| `shop/.env.production.example` | your React repo | The same setting for the live site (next lessons). |

In the shop: `npm install --save-dev vitest`, add `"test": "vitest run"` to `package.json`, then `npm test`: **6 failed** at the start.
In the API: add `<env name="FRONTEND_URL" value="http://localhost:5173"/>` to `phpunit.xml`, then `php artisan test --filter=CorsTest`: **3 failed** at the start.

## Steps
1. Run both servers: `php artisan serve` (API, port 8000) and `npm run dev` (shop, port 5173).
2. Call the API from the shop and read the error in the Console.
3. Publish and set CORS in Laravel: `php artisan config:publish cors`.
4. `apiUrl()` in api.js (TODO 1-2).
5. `request()` in api.js (TODO 3).
6. Load the products in your product list with useEffect: loading, error, "Try again".
7. Run all tests in both repos.
