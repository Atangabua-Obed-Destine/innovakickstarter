# State in a Mobile App (Chapter 7, Mobile)

First add the package:

```bash
flutter pub add provider
```

| File | What it is |
|---|---|
| `lib/state/order_draft.dart` | **Your work.** TODO 1 to 8: the shared order (a ChangeNotifier). |
| `lib/widgets/basket_button.dart` | **Your work.** TODO 9 to 11: the badge that watches the order. |
| `lib/screens/order_draft_screen.dart` | **Your work.** TODO 12 and 13: the order screen. |
| `lib/main.dart` | Puts one `OrderDraft` above the whole app. Ready. |
| `test/helpers.dart` | Shared test helpers. |
| `test/state_test.dart` | 14 self-check tests. |
| `test/navigation_test.dart` | **Updated**: your screens now get an `OrderDraft`. Two of its tests fail until the list screen reads the branch from the draft (lesson step 7). |

You also change two of your own files (see the lesson): the detail screen adds to the draft, and the list screen reads the branch and the customer from it.

## How to use these files
These files go into your `nkwen_rep_app` Flutter project (repository `nkwen-rep-app`). Copy each file to the **same path** in your project. A file with the same name replaces yours.
Then run the self-check from the project folder:

```bash
flutter test
```

Do not change the test files. If a test fails, read its name and its reason: it tells you what to fix.
