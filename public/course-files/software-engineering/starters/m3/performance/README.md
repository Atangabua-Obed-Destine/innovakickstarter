# Chapter 9 practice: a history screen for a cheap phone

A rep's order history with 2,000 orders. It works, but it does far too much work.
Put this folder in your course repository as `ch09-ship-the-app/performance/`.

## Files
| File | What it is |
|---|---|
| `lib/order_history.dart` | **Your work.** Look for the comments SLOW 1, SLOW 2 and SLOW 3. |
| `test/performance_test.dart` | 5 self-check tests that count the work. Do not change them. |

## Steps
1. `flutter pub get`, then `flutter test`. You should see 2 passed and 3 failed. Read each message: it says how much work was done.
2. Fix SLOW 2 (build rows lazily), SLOW 1 (sort once) and SLOW 3 (decode the photo small).
3. Finish when `flutter test` shows 5 passed. Then measure your own rep app, as the lesson shows.
