# Project: Sales-Rep App Screens (Chapter 7, Mobile)

First add the package:

```bash
flutter pub add http
```

| File | What it is |
|---|---|
| `lib/services/nkwen_api.dart` | A small, ready client for the course API (`fetchProducts`). |
| `lib/app.dart`, `lib/main.dart` | The app with its shared objects (the API and the OrderDraft). Ready. |
| `lib/data/payment_methods.dart` | API codes and the labels reps see. Ready. |
| `lib/widgets/branch_dropdown.dart` | **Your work.** TODO A. |
| `test/fixtures/products.json` | The real answer of `GET /products` (20 products), so tests work offline. |
| `test/helpers.dart` | **Updated**: a fake API (MockClient) with the real data. |
| `test/project_m1_test.dart` | 9 project tests. |
| `test/navigation_test.dart`, `test/state_test.dart` | **Updated**: the list now loads from the (fake) API. |

At first `flutter test` does not compile: `ProductListScreen` still asks for `products`. Changing it to load from the API is step 2 of the lesson.
You also change your own `product_list_screen.dart`, `customer_list_screen.dart` and `order_draft_screen.dart` (see the lesson).

## How to use these files
These files go into your `nkwen_rep_app` Flutter project (repository `nkwen-rep-app`). Copy each file to the **same path** in your project. A file with the same name replaces yours.
Then run the self-check from the project folder:

```bash
flutter test
```

Do not change the test files. If a test fails, read its name and its reason: it tells you what to fix.
