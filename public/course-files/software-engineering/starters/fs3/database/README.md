# Starter: Databases for Full-Stack Developers (Chapter 9, Full-stack)

All files go into your Laravel repository (`api/`).

| File | What it is |
|---|---|
| `database/seeders/StockSeeder.php` | Fills `stock_levels` and the reorder levels from `nkwen.json`. Done. |
| `app/Exceptions/NotEnoughStock.php` | Answers 409 with `{message, product_id, requested, available}`. Done. |
| `tests/Feature/StockTest.php` | 4 tests: the stock table, `GET /api/branches/{code}/stock`, the relation. |
| `tests/Feature/OrdersCreateTest.php` | 7 tests: `POST /api/orders` (prices, stock, 422, 409, Idempotency-Key). |

At the start, `php artisan test` shows **10 failed** (one 404 test already passes, because the route does not exist yet).
Copy `database/data/nkwen.json` again from the course files: it now has `reorder_level` for every product.

You write: 3 migrations, 3 models (+ a relation in Product), the stock action in BranchController, StoreOrderRequest, OrderResource, OrderController@store and the routes.
