# Project: Release the Rep App, the order screen

These files go INTO your rep app from Chapters 7 and 8 (`nkwen_rep_app`), at the same paths.
They let a rep open a sent order from the Outbox, take the sandbox mobile-money payment, and add the receipt photo.

## Before you copy
Your rep app must already have, from this chapter's lessons:
- `lib/payments/` (Mobile Money Payment Flow),
- `lib/device/`, `lib/permissions/`, `lib/receipts/`, `lib/reminders/` (Camera & Notifications),
- in `pubspec.yaml`: `http`, `image_picker`, `flutter_local_notifications`, `timezone`, `flutter_timezone`.

## Files
| File | What it is |
|---|---|
| `lib/screens/order_detail_screen.dart` | **Your work:** TODO 1 to 3. |
| `lib/data/order_extras.dart` | Saves the server status and receipt photos on the phone. Ready. |
| `lib/services/order_status_api.dart` | `GET /api/orders/{id}` with `Authorization: Bearer <token>`. For Chapter 10. Ready. |
| `test/order_detail_test.dart` | 6 self-checks. They use your app's own `test/db_helpers.dart`, `test/helpers.dart` and `test/support/`. |

## Steps
1. Copy the files. Run `flutter test test/order_detail_test.dart`. You should see all 6 fail (TODO 1 to 3, and the Outbox tap).
2. Finish TODO 1 to 3 in `order_detail_screen.dart`.
3. In `lib/screens/outbox_screen.dart`, give the "Sent" `ListTile` an `onTap` that opens `OrderDetailScreen(order: order)` (import `order_detail_screen.dart`).
4. Run `flutter test`. Every test passes: yours from Chapters 7 and 8, and these 6.

The payment starts only from an order the server already has (`serverId`, `serverTotalFcfa`, `serverStatus == 'awaiting_payment'`). A draft has no server id. The phone field is never prefilled: in the sandbox only MoMo test numbers work.
