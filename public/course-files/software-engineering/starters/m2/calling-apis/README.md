# Calling APIs from the App (Chapter 8, Mobile)

| File | What it is |
|---|---|
| `lib/services/nkwen_api.dart` | **Your work.** A new client with TODO 1 to 6. It replaces the small one from Chapter 7. |
| `lib/models/branch.dart` | **Your work.** TODO 7: `Branch.fromJson`. |
| `test/nkwen_api_test.dart` | 11 tests for the client, with a pretend server (MockClient). |
| `test/product_list_states_test.dart` | 4 tests for the list's states: loading, error, empty (search) and pull-to-refresh. |

You also change your own `product_list_screen.dart`: a search box, the four states and pull-to-refresh (lesson step 6).

## How to use these files
These files go into your `nkwen_rep_app` Flutter project (repository `nkwen-rep-app`). Copy each file to the **same path** in your project. A file with the same name replaces yours.
Then run the self-check from the project folder:

```bash
flutter test
```

Do not change the test files. If a test fails, read its name and its reason: it tells you what to fix.
