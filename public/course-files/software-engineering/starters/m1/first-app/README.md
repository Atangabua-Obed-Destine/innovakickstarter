# Setting Up Flutter & Your First App (Chapter 7, Mobile)

Start from a fresh project:

```bash
flutter create --org cm.nkwentraders --platforms android nkwen_rep_app
```

Then copy these files into it. They replace `lib/main.dart` and `test/widget_test.dart`.

| File | What it is |
|---|---|
| `lib/main.dart` | Starts the app and opens `VisitsScreen`. Ready. |
| `lib/screens/visits_screen.dart` | **Your work.** A "shops visited today" counter with 5 TODOs. |
| `test/widget_test.dart` | 5 self-check tests. At first 4 fail, each naming a TODO. |

Run `flutter test`, then `flutter run` with your phone connected. Hot reload (`r`) after each TODO.

## How to use these files
These files go into your `nkwen_rep_app` Flutter project (repository `nkwen-rep-app`). Copy each file to the **same path** in your project. A file with the same name replaces yours.
Then run the self-check from the project folder:

```bash
flutter test
```

Do not change the test files. If a test fails, read its name and its reason: it tells you what to fix.
