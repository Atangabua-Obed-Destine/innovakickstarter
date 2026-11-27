# Crash report: Nkwen Rep app, version 1.3.0 (build 7)

Sent by Mrs. Fon Lydia (Commercial Avenue) on behalf of three reps, after a week of testing on their own phones.
The reps' phones are cheap Android phones (Android 10 to 13, 2 to 3 GB of memory). The app was a debug build, so the errors are readable.

---

## Crash 1: the order list closes the app
**What the rep did:** took an order for Ndi Grace in a market with no network, then opened "My orders".
**What happened:** red screen, then the app closed. It happens again every time "My orders" opens, until the phone gets network and the order syncs.

```
type 'Null' is not a subtype of type 'int' in type cast
package:nkwen_crash_report/orders.dart 19:37  new Order.fromJson
```

## Crash 2: "Clear delivered" crashes, sometimes
**What the rep did:** delivered two orders in a row, then tapped "Clear delivered".
**What happened:** the app crashed. With only one delivered order, it works. One rep says a delivered order "came back" after clearing.

```
RangeError (length): Invalid value: Not in inclusive range 0..2: 3
dart:core                                     List.[]
package:nkwen_crash_report/orders.dart 31:14  clearDelivered
```

## Crash 3: an old order card shows a grey error box
**What the rep did:** scrolled to an order saved by version 1.0 of the app (in March), from the Mile 4 branch.
**What happened:** the order card is a grey box (red in debug), and the rest of the list does not draw.

```
Bad state: No element
dart:collection                                 ListBase.firstWhere
package:nkwen_crash_report/branches.dart 12:27  branchName
```

## Crash 4: leaving the payment screen early
**What the rep did:** started a payment check on a slow network, got bored, tapped Back.
**What happened:** a few seconds later this appeared in the log. In the release build the app sometimes closed instead.

```
setState() called after dispose(): _PaymentStatusScreenState#71ccd(lifecycle state: defunct, not mounted)
#0      State.setState.<anonymous closure> (package:flutter/src/widgets/framework.dart:1165:9)
#1      State.setState (package:flutter/src/widgets/framework.dart:1202:6)
#2      _PaymentStatusScreenState._check (package:nkwen_crash_report/payment_status_screen.dart:25:5)
```
