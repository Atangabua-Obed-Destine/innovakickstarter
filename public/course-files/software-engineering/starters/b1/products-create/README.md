# Starter: Handling POST Requests (Chapter 7, Backend)

One file to add to your `nkwen-products-api` app:

- `tests/Feature/ProductsCreateTest.php`: 7 self-check tests for `POST /api/products`.

Copy it to the same path in your app, then run:

```
php artisan test --filter=ProductsCreateTest
```

Before you start, most tests fail with `received 405` ("Method Not Allowed"): there is no POST route yet.
The last test (`errors are json even when the client forgets the accept header`) only passes
once your middleware is in place.

When the lesson is finished, all 7 pass, and so does the whole suite (`php artisan test`).
