# Starter: Project, Nkwen Online Orders (Full App) (Chapter 9, Full-stack)

The whole app, live: the React shop, your Laravel API, MySQL and staff logins. Orders follow the Nkwen Orders API, the same one the Backend pathway builds.

| File | Goes in | What it is |
|---|---|---|
| `contract.json` | anywhere | The 15 checks the platform runs against your **live API**. |
| `check-contract.mjs` | anywhere | Runs them: `node check-contract.mjs https://YOUR-API.onrender.com` |
| `check-fullstack.mjs` | anywhere | Shop + API talk check: `node check-fullstack.mjs <shop URL> <API URL>` |
| `api/tests/Feature/OrdersProjectTest.php` | Laravel | 6 tests for the new endpoints: they describe the rules. Make them pass, then add 2 of your own. |
| `shop/e2e/orders.spec.js` | React | 1 worked end-to-end test, 2 TODOs. |

## The API you finish
| Request | Who | Success | Errors |
|---|---|---|---|
| `GET /api/orders?branch=CAV` | staff | 200 `{data: [orders], meta: {count}}`, newest first; head office: any branch | 401 |
| `GET /api/orders/{id}` | staff of that branch, head office | 200 `{data: order}` | 401, 403, 404 |
| `PATCH /api/orders/{id}` `{"status": "cancelled"}` | branch manager, head office | 200; stock goes back | 401, 403, 404, 409 (not allowed now), 422 (unknown status) |

Status changes: `pay_at_counter` or `awaiting_payment` -> `paid` -> `delivered`; `cancelled` only from the first two. Mobile-money orders become `paid` only through the payment callback (Backend Chapter 9): answer 409 to a PATCH to `paid` for them. Asking for the status an order already has is safe: 200.

## User stories for the shop (tick them in your README)
1. As a customer, I can browse products and prices without logging in.
2. As a customer, I see the stock badge for the branch I collect from, and it changes when I change branch.
3. As a member of staff, I log in once, and a refresh keeps me logged in.
4. As a new cashier, I can create my account at my branch.
5. As a cashier, I can place an order for a customer (name, payment method), and I see its number and total.
6. As a cashier, I cannot place an order at another branch: I see the API's message.
7. As a cashier, I see clear messages for a missing name (422) and not enough stock (409).
8. As a cashier, a double tap on "Place order" never makes two orders.
9. As staff, I see my branch's orders, newest first; head office sees all.
10. As a branch manager, I can cancel an order that is not paid yet; a cashier sees no Cancel button.
11. As staff, I can log out, and the token stops working.
12. As anyone, when the API is asleep or the network fails, I see a clear message and can try again.
