# Chapter 9 practice: a sandbox mobile-money payment

The rep's payment screen for one order, with a pretend server and a pretend MoMo behind it.
**No real money moves.** The sandbox only accepts MTN's sandbox test numbers.
Put this folder in your course repository as `ch09-ship-the-app/payment-flow/`.

## Files
| File | What it is |
|---|---|
| `lib/payments/payment_api.dart` | What the app can ask the Nkwen server. Read it first. |
| `lib/payments/sandbox_payment_api.dart` | The pretend server. Do not change it. |
| `lib/payments/payment_flow.dart` | **Your work:** `pay()` and `_waitForResult()` (TODO 1 to 6). |
| `lib/payments/pay_screen.dart` | The screen. It already follows the flow. |
| `lib/main.dart` | Run it on your phone: `flutter run`. |
| `lib/payments/http_payment_api.dart` | For Chapter 10: the same API against a real server, with the Bearer token. Ready. |
| `test/` | 16 self-check tests. Do not change them. |

## Sandbox test numbers (copied from MTN MoMo's sandbox)
| Number | What the "customer" does |
|---|---|
| 46733123450 | the payment fails |
| 46733123451 | the customer declines |
| 46733123452 | the customer is too slow: it expires |
| 46733123453 | no answer, ever (stays pending) |
| 46733123454 | answers, but slowly |
| 46733123455 | no MoMo account for this number |
| 46733123456 to 46733123459 | the customer approves |

## Steps
1. `flutter pub get`, then `flutter test`. You should see 3 passed and 13 failed with `UnimplementedError: TODO ...`.
2. Write `pay()` (TODO 1 to 4), then `_waitForResult()` (TODO 5 and 6).
3. Run `flutter test` after each TODO. Read the `reason:` of any failing test: it explains the rule.
4. Finish when `flutter test` shows 16 passed. Then `flutter run` and try every test number.

The files import `package:material_ui/material_ui.dart`, like your rep app. Never prefill the phone field with a customer's saved number: the sandbox needs a test number.
