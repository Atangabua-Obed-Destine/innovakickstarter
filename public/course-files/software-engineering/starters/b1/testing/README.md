# Starter: Testing Your API (Chapter 7, Backend)

One file to add to your `nkwen-products-api` app:

- `tests/Feature/ProductApiTest.php`: one finished example test and seven TODO tests.

Copy it to the same path in your app, then run:

```
php artisan test --filter=ProductApiTest
```

At first you see 1 passing test and 7 failing ones. Each failure says `TODO: write ...`.
Replace each `$this->fail('TODO ...')` line with a real test, using the values in
`database/data/nkwen.json`.

The three search tests are special. Write them **before** you build `?search=`.
Watch them fail (red). Then build the search and watch them pass (green).
