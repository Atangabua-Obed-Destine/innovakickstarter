# Offline-First Orders & Sync (Chapter 8, Mobile)

Needs the files from "Calling APIs from the App" and "Storing Data on the Phone".

| File | What it is |
|---|---|
| `lib/services/order_answer.dart` | What the server answered to POST /orders. Ready. |
| `lib/services/orders_api.dart` | **Your work.** TODO 1 to 3: `postOrder` with the Idempotency-Key. |
| `lib/sync/order_queue.dart` | **Your work.** TODO 4 to 7: queue queries on `OrderStore`. |
| `lib/sync/sync_service.dart` | **Your work.** TODO 8 and 9: the sync. |
| `test/support/fake_orders_server.dart` | A pretend Orders API with the same key rules as the practice API. It can also lose answers, fail with 503 or go offline on purpose. |
| `test/sync_test.dart` | 12 tests, including two "NO DUPLICATES" tests. |

The practice API (`POST /orders`) checks and prices an order and saves nothing. It honours `Idempotency-Key`: the same key and order returns the first order again (header `Idempotent-Replayed: true`), and the same key with a different order gives 422. The tests use a fake server with the same rules, so they can lose answers and fail on purpose.

## How to use these files
These files go into your `nkwen_rep_app` Flutter project (repository `nkwen-rep-app`). Copy each file to the **same path** in your project. A file with the same name replaces yours.
Then run the self-check from the project folder:

```bash
flutter test
```

Do not change the test files. If a test fails, read its name and its reason: it tells you what to fix.
