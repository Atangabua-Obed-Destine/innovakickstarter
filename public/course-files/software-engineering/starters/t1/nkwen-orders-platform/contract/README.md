# Nkwen Orders Platform: the API contract

Version: 2.0 (squad copy)
Agreed by: TODO (the GitHub usernames of everyone in the squad, for example @amina-dev @tabi-codes @ngwa-flutter)
Date agreed: TODO (YYYY-MM-DD)

This file is the promise between the three parts of the platform. The **Orders API** (backend) keeps it.
The **web shop** (frontend) and the **rep app** (mobile) rely on it. Everyone agrees it **before** building,
so all three parts can be built at the same time.

It grows from the Orders API you designed in Chapter 6 and from the Backend pathway's APIs: the
Orders & Inventory API (Chapter 8) and the Secure Multi-Role Staff API (Chapter 9). Staff logins, roles,
the stock rows and the demo accounts are exactly those of Chapter 9, so the backend members can bring that code in.
Version 2 adds two things for the squad: **guest orders** (customers and reps order without a staff login)
and the `channel` field.

## How to change this contract
1. Open an issue that says what you want to change and why.
2. Change this file **and** the examples in `contract/examples/` in one pull request.
3. Someone from **every part that uses the endpoint** must approve it (API, web, app).
4. **Adding** a field or an endpoint is safe. **Renaming or removing** one breaks the other teams: avoid it.

Lines marked **(checked)** are the ones the I-NNOVA platform checks on your live API. Do not change them.

## 1. Basics
- Base URL: the API's address plus `/api`. Locally `http://127.0.0.1:8000/api`; the stand-in uses port 8787.
- Every request and answer is JSON. Clients send `Accept: application/json`.
- Prices are **whole FCFA** (integers), never decimals, and always with `"currency": "FCFA"`. **(checked)**
- Lists look like `{"data": [...], "meta": {"count": n}}`. One item looks like `{"data": {...}}`. **(checked)**
- Errors look like `{"message": "..."}`. A 422 also has `"errors": {"field": ["..."]}` (the Laravel style). **(checked)**
- Shelf prices already include VAT (19.25%). The API never adds VAT on top.

## 2. Resources
| Resource | Fields |
|---|---|
| product | `id` (int), `sku`, `name`, `category`, `unit`, `price_fcfa` (int), `currency` |
| branch | `code` (the id, for example `CAV`), `name`, `city`, `region`, `manager`, `opened_year` (int), `status` (`open` or `planned`) |
| stock row | `product_id`, `branch_code`, `sku`, `name`, `quantity` (int), `reorder_level` (int), `status`: the Chapter 1 rule, `RESTOCK` at or below the reorder level, `LOW` at or below twice the reorder level, otherwise `OK` |
| order | `id` (`ORD-` + 8 letters or digits), `branch`, `customer_name`, `payment_method`, `channel` (`web` or `rep_app`), `status`, `items`, `total_fcfa`, `currency`, `created_at` (ISO 8601) |
| order item | `product_id`, `name`, `quantity`, `unit_price_fcfa`, `line_total_fcfa` |
| staff | `id`, `name`, `email`, `role` (`cashier`, `branch_manager` or `head_office`), `branch` (a code, or `null` for head office), `is_active` |

## 3. Endpoints
| Method and path | Who | What it does | Success | Errors |
|---|---|---|---|---|
| `GET /api/products` | anyone | All products. Filters: `?category=`, `?search=`, `?branch=` (only products in stock there, each with `stock`), `?sort=price`, `-price` or `name` | 200 **(checked)** | 404 unknown branch |
| `GET /api/products/{id}` | anyone | One product | 200 | 404 |
| `GET /api/branches` | anyone | The 5 branches, in this order: NKW, ML4, CAV, BAF, LMB | 200 **(checked)** | |
| `GET /api/branches/{code}/stock` | anyone | Stock of every product at one branch (Limbe, still planned, has none yet) | 200 **(checked)** | 404 |
| `POST /api/orders` | anyone (guest), or staff | Create an order and take the stock. See section 3a for staff. | 201 and a `Location` header **(checked)** | 422, 409 **(checked)**, 403 staff at another branch |
| `GET /api/orders/{id}` | anyone with the order id | One order (the id works like a tracking number) | 200 **(checked)** | 404 **(checked)** |
| `POST /api/login` | staff | Log in: `{email, password}` | 200 `{"token", "token_type": "Bearer", "data": staff}` **(checked)** | 401 wrong email or password, 403 deactivated, 429 too many tries |
| `POST /api/logout`, `GET /api/me` | staff | End the token; who am I | 204 / 200 | 401 |
| `GET /api/orders` | staff | List orders. Filters: `?branch=`, `?status=`, `?from=2026-12-01` (newest first) | 200 **(checked)** | 401 **(checked)**, 403 another branch, 422 bad filter |
| `PATCH /api/orders/{id}` | staff | Change the status: body `{"status": "delivered"}` | 200 **(checked)** | 401, 403, 404, 422 unknown status, 409 change not allowed **(checked)** |

The Chapter 9 API has more endpoints (register a cashier, restock, stock history, mobile-money payments and
their signed callback, the staff list). Keep them: the clients do not need them for this release.

### 3a. Who may do what (the Chapter 9 permission table, plus guests)
| Action | Guest (no token) | Cashier | Branch manager | Head office |
|---|---|---|---|---|
| Read products, branches, branch stock | yes | yes | yes | yes |
| Create an order | any open branch | own branch | own branch | any open branch |
| Read one order by its id | yes | yes | yes | yes |
| List orders | no (401) | own branch | own branch | all |
| Mark paid (cash) or delivered | no (401) | own branch | own branch | all |
| Cancel (before payment) | no (401) | no (403) | own branch | all |
| Mark a mobile-money order paid | no one: only a verified payment callback (a PATCH answers 409) | | | |

**Guest orders are the one change to the Chapter 9 code.** There, every order needs a staff token. Customers on
the web shop and reps on the road have no staff account, so `POST /api/orders` without a token must work, and
`GET /api/orders/{id}` must work for anyone who has the id. With a token, the Chapter 9 rules still apply.

There is no `DELETE` for orders. Orders are business and tax records: we **cancel**, we never delete.

Staff send their token in a header: `Authorization: Bearer <token>`. Without it, staff endpoints answer **401**.
With a token but without permission (for example the Nkwen manager listing Commercial Avenue's orders), they answer **403**.

## 4. Creating an order
Request (`contract/examples/order-request.json`). The client sends product ids and quantities, **never prices**:
```json
{
  "branch": "CAV",
  "customer_name": "Mbah Clarisse",
  "payment_method": "mtn_momo",
  "channel": "web",
  "items": [ { "product_id": 6, "quantity": 2 }, { "product_id": 15, "quantity": 1 } ]
}
```
Answer **201**, with `Location: /api/orders/ORD-7Q2K9X1B` (`contract/examples/order-created.json`). The server copies each unit price onto the order, so later price changes do not change old orders.

Rules:
- `branch`: required, an **open** branch (NKW, ML4, CAV or BAF). Limbe (LMB) is planned, so it is refused.
- `customer_name`: required, 2 to 100 characters.
- `payment_method`: required, one of `cash`, `mtn_momo`, `orange_money`, `bank_transfer`.
- `channel`: optional, `web` (the default) or `rep_app`.
- `items`: required, at least one. Each `product_id` must exist. Each `quantity` is a whole number from 1 to 100.
- If the same product appears in two lines, the quantities are **added** before the stock check.
- Not enough stock at that branch: **409** with `message`, `product_id`, `requested` and `available` (`examples/error-409.json`). Nothing is saved.
- The stock check and the stock change happen in **one database transaction**, so two customers cannot both buy the last bag.

### The Idempotency-Key header (no duplicate orders)
The rep app works on bad networks. Sometimes an order reaches the server but the answer is lost, and the app tries again.
To stop that creating a second order, the client sends a header with a unique value **per order** (a UUID is best):
```
Idempotency-Key: 5f0c1b9e-3c1d-4a43-9a52-2f1d2b7c8e11
```
- The first time the server sees a key, it creates the order and answers 201.
- If the key comes again, the server creates **nothing**. It answers **200** with the order it created the first time. **(checked)**
- The client creates the key once, when the order is made, and keeps it with the order until it is synced.
- Keys are 1 to 100 characters. The web shop sends one too, so a double click cannot order twice.

## 5. Statuses
```
pay_at_counter (cash) ----+
                          +--> paid --> delivered
awaiting_payment (other) -+
pay_at_counter or awaiting_payment --> cancelled  (the stock goes back to the shelf)
```
Any other change answers **409**, for example delivering a cancelled order. **(checked)** An unknown status answers 422.
A mobile-money order becomes `paid` only through the verified payment callback from Chapter 9, so `PATCH` to `paid` on it answers 409.

## 6. Demo accounts (Chapter 9's `StaffSeeder`; seed them on every environment)
| Email | Password | Role | Branch |
|---|---|---|---|
| `headoffice@nkwen.test` | `Nkwen-Demo-2026` | head_office | (none) |
| `manager.nkw@nkwen.test` | `Nkwen-Demo-2026` | branch_manager | NKW **(checked)** |
| `cashier.nkw@nkwen.test` | `Nkwen-Demo-2026` | cashier | NKW |

These are public demo passwords for a training project. Never reuse them for anything real.

Login answer (`examples/login.json`): the token at the top level, the staff member in `data`:
`{"token": "...", "token_type": "Bearer", "data": {"id", "name", "email", "role", "branch", "is_active"}}`. **(checked)**

## 7. Cross-origin requests (CORS)
The web shop runs on a different address from the API, so the API must allow it:
- Locally: `http://localhost:5173` (Vite).
- Live: TODO (the web shop's live address, once you know it).

## 8. Decisions we made in System Design
Answer each question in one or two sentences, with the reason. Change the stand-in API if your answer differs from what it does now.
1. What does a guest order need besides the customer's name (for example a phone number for the pickup call), and why? TODO
2. How long can an order stay `awaiting_payment` before it is cancelled and the stock goes back? Who or what cancels it? TODO
3. Does a sales rep need to log in to send orders, or is `channel: rep_app` enough for now? TODO
4. One more decision of your own: TODO

## 9. Examples and tests
- `contract/examples/`: one real example of every request and answer. Teams use them in their own tests.
- `contract/contract.test.mjs`: checks that an API keeps this contract (`node --test "contract/*.test.mjs"`).
- `contract/platform-checks.json` and `contract/check-contract.mjs`: the 15 checks the platform runs on the live API.
- `contract/stand-in-api.mjs`: a stand-in that keeps this contract in memory (`node contract/stand-in-api.mjs`).
