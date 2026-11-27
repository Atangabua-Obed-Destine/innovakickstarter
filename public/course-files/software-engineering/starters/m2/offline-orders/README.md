# Project: Offline Order Taking (Chapter 8, Mobile)

Needs every file from Chapter 8.

| File | What it is |
|---|---|
| `lib/app.dart`, `lib/main.dart` | **Updated**: provide `OrdersApi`, `OrderStore`, `ProductCache` and `SyncService`, and sync when the app opens. Ready. |
| `test/support/test_app.dart` | Starts the whole app with a test database, a fake products API and a fake Orders API. |
| `test/project_m2_test.dart` | 6 end-to-end tests: offline list, offline order then send, no duplicates, double Save, 409. |
| `pubspec.reference.yaml` | The package versions we tested with (Flutter 3.50.1). Compare with your `pubspec.yaml` if a package will not resolve. Do not rename it. |

You change your own `product_list_screen.dart`, `order_draft_screen.dart` and `outbox_screen.dart` (see the lesson). Every earlier test must still pass.

## How to use these files
These files go into your `nkwen_rep_app` Flutter project (repository `nkwen-rep-app`). Copy each file to the **same path** in your project. A file with the same name replaces yours.
Then run the self-check from the project folder:

```bash
flutter test
```

Do not change the test files. If a test fails, read its name and its reason: it tells you what to fix.
