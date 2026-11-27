# Storing Data on the Phone (Chapter 8, Mobile)

First add the packages:

```bash
flutter pub add sqflite path uuid
flutter pub add dev:sqflite_common_ffi
```

| File | What it is |
|---|---|
| `lib/data/local_db.dart` | Opens the database and creates the tables. Ready: read it. |
| `lib/models/saved_order.dart` | An order saved on the phone, and its API body. Ready. |
| `lib/data/product_cache.dart` | **Your work.** TODO 1 to 4. |
| `lib/data/order_store.dart` | **Your work.** TODO 5 and 6. |
| `lib/screens/outbox_screen.dart` | **Your work.** TODO 7 and 8. |
| `lib/app.dart`, `lib/main.dart` | **Updated**: open the database and provide the stores. If you changed the theme, copy your change back. |
| `test/db_helpers.dart` | Opens an empty test database in memory. |
| `test/storage_test.dart` | 8 tests on a real SQLite database. |

## How to use these files
These files go into your `nkwen_rep_app` Flutter project (repository `nkwen-rep-app`). Copy each file to the **same path** in your project. A file with the same name replaces yours.
Then run the self-check from the project folder:

```bash
flutter test
```

Do not change the test files. If a test fails, read its name and its reason: it tells you what to fix.
