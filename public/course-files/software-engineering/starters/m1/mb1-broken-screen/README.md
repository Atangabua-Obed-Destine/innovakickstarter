# Problem Solving MB1: Fix the Broken Screen (Chapter 7, Mobile)

A teammate's Quick order screen, on its own in `lib/mb1/`. It does not touch the rest of your app.

| File | What it is |
|---|---|
| `lib/mb1/main_mb1.dart` | Runs the screen alone: `flutter run -t lib/mb1/main_mb1.dart` |
| `lib/mb1/quick_order_screen.dart`, `lib/mb1/quantity_row.dart` | **The five bugs are in these two files.** |
| `lib/mb1/mb1_data.dart`, `lib/mb1/mb1_cart.dart` | Data and the cart. No bugs here. |
| `test/mb1_test.dart` | 5 tests, one per symptom in the bug report. At first all fail. |

Run only these tests with `flutter test test/mb1_test.dart`. Create `FIXLOG.md` next to `pubspec.yaml` with one row per bug:

| # | Symptom | Error message (or "none") | Root cause | Fix | How I checked |
|---|---|---|---|---|---|

Fix one bug at a time. Some bugs only show after another is fixed.
