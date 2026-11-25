# Starter: Project: Nkwen Products API (Chapter 7, Backend)

These files go into your `nkwen-products-api` app (or into your own project if you use another stack).
They are **not** a whole Laravel project. Copy each file to the same path in your app.

| File | What it is | Other stacks |
|---|---|---|
| `contract.json` | The API contract: the 14 checks the platform runs against your live API. | Use it as it is. |
| `check-contract.mjs` | Runs the contract against any URL: `node check-contract.mjs http://127.0.0.1:8000`. Needs Node 18+. | Use it as it is. |
| `tests/Feature/ProjectEndpointsTest.php` | 10 TODO feature tests for update, delete and branches. | Write the same tests in your test tool. |
| `Dockerfile`, `docker/start.sh`, `.dockerignore` | Lets Render (or any Docker host) build and run your Laravel app. | Write your own Dockerfile. |

## The API you must build

Every response is JSON. A **product** looks like this:

```json
{"id": 6, "sku": "NKT-GRA-006", "name": "Rice 50kg", "category": "Grains", "unit": "bag", "price_fcfa": 36005, "currency": "FCFA"}
```

| Request | Success | Errors |
|---|---|---|
| `GET /api/products` | 200 `{"data": [products], "meta": {"count": n}}`, ordered by id | |
| `GET /api/products?category=Grains` | 200, only that category (any letter case) | |
| `GET /api/products?search=rice` | 200, names that contain the text (any letter case) | |
| `GET /api/products/{id}` | 200 `{"data": product}` | 404 `{"message": "..."}` |
| `POST /api/products` | 201 `{"data": product}` | 422 `{"message": "...", "errors": {"field": ["..."]}}` |
| `PUT` or `PATCH /api/products/{id}` | 200 `{"data": product}`. Send only the fields to change. | 404, 422 |
| `DELETE /api/products/{id}` | 204, empty body | 404 |
| `GET /api/branches` | 200 `{"data": [branches], "meta": {"count": 5}}`, in the order of `nkwen.json` | |

A **branch** looks like `{"code": "NKW", "name": "Nkwen", "city": "Bamenda", "region": "North-West", "manager": "Mrs. Enow Patience", "opened_year": 2019, "status": "open"}`.

Validation rules (create and update):

| Field | Rule |
|---|---|
| `sku` | required, text, at most 30 characters, not used by another product |
| `name` | required, text, 2 to 100 characters |
| `category` | required, one of: Bakery, Dairy, Grains, Groceries, Household, Oils, Produce |
| `unit` | required, text, at most 20 characters |
| `price_fcfa` | required, whole number from 1 to 10,000,000 |

## Check before you submit

```
php artisan test
node check-contract.mjs http://127.0.0.1:8000
node check-contract.mjs https://YOUR-APP.onrender.com
```

All three must be green. On a free host, open your URL in the browser first, to wake the server up.
