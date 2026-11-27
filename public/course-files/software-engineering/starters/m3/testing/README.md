# Chapter 9 practice: testing the rep's basket

A small Flutter project with a basket, a screen and some tests to finish.
Put this folder in your course repository as `ch09-ship-the-app/testing/`.

## Files
| File | What it is |
|---|---|
| `lib/basket.dart` | The basket: add, change quantity, total. **One real bug is hiding here.** |
| `lib/money.dart` | `formatFcfa()`: 78610 becomes "78,610 FCFA". |
| `lib/basket_screen.dart` | The screen where a rep builds an order. |
| `lib/main.dart` | A tiny app that shows the screen with 5 Nkwen products. |
| `test/basket_test.dart` | Unit tests. 1 example, 5 TODOs. |
| `test/basket_screen_test.dart` | Widget tests. 1 example, 4 TODOs. |
| `integration_test/app_test.dart` | One integration test, for a phone or emulator. |

## Steps
1. `flutter pub get`, then `flutter test`. You should see 2 passed and 9 failed, each saying `TODO: write ...`.
2. Write the unit tests first, then the widget tests. Run `flutter test` after each one.
3. Some of your tests will fail because of the bug in `lib/basket.dart`. Fix the bug, not the test.
4. With a phone or emulator connected: `flutter test integration_test/app_test.dart`.
5. Finish when `flutter test` shows 11 passed and no `TODO` is left.
