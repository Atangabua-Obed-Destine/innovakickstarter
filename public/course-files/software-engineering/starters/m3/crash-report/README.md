# Problem Solving MB3: the crash report

Three reps sent crash reports from their phones. Each test in `test/crash_test.dart` replays one crash with the same data.
Put this folder in your course repository as `ch09-ship-the-app/crash-report/`.

## Files
| File | What it is |
|---|---|
| `CRASH_REPORT.md` | What the reps did, and the errors their phones showed. Start here. |
| `lib/` | The app code with the bugs. |
| `test/crash_test.dart` | 7 tests: 4 crashes and 3 checks. Do not change them. |
| `FINDINGS.md` | **Your work:** what you found and fixed, crash by crash. |

## Steps
1. `flutter pub get`, then `flutter test`. You should see 1 passed and 6 failed, with the same errors as the report.
2. For each crash: read the error, find the line, write the root cause in `FINDINGS.md`, then fix the code.
3. Finish when `flutter test` shows 7 passed and `FINDINGS.md` is complete.
