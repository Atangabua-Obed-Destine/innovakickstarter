# Chapter 9 practice: receipt photos and delivery reminders

The rules behind two phone features, plus the real plugin code to try on your phone.
Put this folder in your course repository as `ch09-ship-the-app/camera-notifications/`.

## Files
| File | What it is |
|---|---|
| `lib/reminders/delivery_reminders.dart` | **Your work:** `reminderTimes()` (TODO 1 to 3). |
| `lib/permissions/permission_coach.dart` | **Your work:** `nextStep()` (TODO 4): when and how to ask. |
| `lib/receipts/receipt_rules.dart` | **Your work:** `receiptFileName()` (TODO 5). |
| `lib/device/phone_features.dart` | The real camera and notification code (plugins). Read it; tests do not use it. |
| `lib/main.dart` | A demo screen for your phone. |
| `test/device_rules_test.dart` | 18 self-check tests. Do not change them. |

## Steps
1. `flutter pub get`, then `flutter test`. You should see 6 passed and 12 failed with `UnimplementedError: TODO ...`.
2. Finish the TODOs until `flutter test` shows 18 passed.
3. To run the demo on your phone, follow "Try it on your phone" in the lesson. It adds the `android/` folder, then changes Gradle and the manifest.
