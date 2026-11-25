# Starter: Routes, Controllers & JSON Responses (Chapter 7, Backend)

These are **not** a whole Laravel project. They are four files you add to the
`nkwen-products-api` app you created in Setup. The folders match your app, so
copy each file to the same path inside your app:

| File | What it is |
|---|---|
| `database/data/nkwen.json` | The Nkwen Traders data (20 products, 5 branches), the same as the course API. |
| `database/seeders/ProductSeeder.php` | A seeder: code that fills the `products` table from `nkwen.json`. |
| `tests/Feature/ProductsReadTest.php` | Self-check tests. They compare your API with `nkwen.json`. |
| `README.md` | This file. You do not need to copy it. |

## Check your work

```
php artisan test --filter=ProductsReadTest
```

Before you build anything, these tests fail with messages such as
`no such table: products` or `Failed asserting that 404 is identical to 200`.
That is normal: they describe what you have not built yet.

When the lesson is finished, all 6 tests pass. Do not edit the tests to make them pass.
