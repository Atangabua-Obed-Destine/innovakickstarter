import 'dart:math';

import 'package:flutter/foundation.dart';

import 'payment_api.dart';

/// Where the rep is in the payment.
enum PayStep {
  ready, // waiting for the rep to enter the customer's number
  sending, // asking the server to start the payment
  waiting, // the customer must approve on their phone; we check the server
  paid, // the SERVER confirmed: the order is paid
  failed, // the server said no (rejected, expired, refused...)
  unknown, // we do not know yet: never say paid, never take a second payment
}

/// Runs one mobile-money payment for one order, the safe way.
class PaymentFlow extends ChangeNotifier {
  PaymentFlow({
    required this.api,
    required this.orderId,
    required this.totalFcfa,
    this.sandbox = true,
    this.checkDelays = defaultCheckDelays,
    Future<void> Function(Duration)? wait,
    String Function()? newKey,
  })  : _wait = wait ?? ((d) => Future<void>.delayed(d)),
        _newKey = newKey ?? newIdempotencyKey;

  /// Wait a little longer before each check (2 s, 3 s, 5 s...): about 4 minutes
  /// in all, under MoMo's 5-minute approval limit, without flooding the server.
  static const defaultCheckDelays = [
    Duration(seconds: 2), Duration(seconds: 3), Duration(seconds: 5),
    Duration(seconds: 8), Duration(seconds: 13), Duration(seconds: 21),
    Duration(seconds: 34), Duration(seconds: 55), Duration(seconds: 89),
  ];

  final PaymentApi api;
  final String orderId;
  final int totalFcfa; // from the server's order, never typed by the rep
  final bool sandbox;
  final List<Duration> checkDelays;
  final Future<void> Function(Duration) _wait;
  final String Function() _newKey;

  PayStep step = PayStep.ready;
  String message = '';
  String? reference; // the payment's id on the server, once we know it
  String? _key; // the Idempotency-Key of the current attempt

  bool get busy => step == PayStep.sending || step == PayStep.waiting;

  void _go(PayStep next, [String text = '']) {
    step = next;
    message = text;
    notifyListeners();
  }

  /// Asks the server to start a payment for this order from [phone].
  Future<void> pay(String phone) async {
    // TODO 1: If a payment is already running (busy) or the order is paid, do nothing.
    //         A rep who taps twice must not start two payments.
    // TODO 2: Check the number with phoneProblem(phone, sandbox: sandbox).
    //         If there is a problem: put it in message, call notifyListeners(), and stop.
    // TODO 3: Choose the Idempotency-Key for this attempt (_key):
    //         - a NEW key (_newKey()) for a new attempt, and clear reference;
    //         - the SAME key if the last attempt ended unknown with no reference
    //           (the request may have reached the server).
    //         Then go to PayStep.sending.
    // TODO 4: Call api.startPayment(...) up to 3 times.
    //         - NetworkException: try again with the SAME key.
    //         - PaymentRefused: go to failed, with the server's message. Stop.
    //         - Success (202): save the reference, go to WAITING (not paid!) with a message
    //           asking the customer to approve fcfa(totalFcfa), then await _waitForResult().
    //         If all 3 tries had no network: go to unknown, with a calm message.
    throw UnimplementedError('TODO: write pay() (steps 1 to 4 in payment_flow.dart)');
  }

  /// Asks the server about the payment until it is paid or failed.
  Future<void> _waitForResult() async {
    // TODO 5: For each delay in checkDelays: await _wait(delay), then
    //         api.checkPayment(orderId: orderId, reference: reference!).
    //         - successful AND orderStatus 'paid': go to paid. Stop.
    //         - failed: go to failed with friendlyReason(check.reason). Stop.
    //         - pending, or a NetworkException: keep waiting (a lost check is not a failure).
    // TODO 6: If no delays are left: go to unknown, telling the rep not to take
    //         a second payment and to check again later.
    throw UnimplementedError('TODO: write _waitForResult() (steps 5 and 6 in payment_flow.dart)');
  }

  /// The rep taps "Check again" after an unknown result.
  Future<void> checkAgain() async {
    if (busy || reference == null) return;
    _go(PayStep.waiting, 'Checking the payment again...');
    await _waitForResult();
  }
}

/// Returns a kind message if [phone] cannot be used, or null if it is fine.
String? phoneProblem(String phone, {required bool sandbox}) {
  final digits = phone.replaceAll(RegExp(r'[\s-]'), '');
  if (sandbox) {
    return RegExp(r'^4673312345\d$').hasMatch(digits)
        ? null
        : 'Sandbox mode: use a test number from 46733123450 to 46733123459. Never a real customer number.';
  }
  return RegExp(r'^(\+?237)?6\d{8}$').hasMatch(digits)
      ? null
      : 'Enter a Cameroon mobile number, for example 6XX XXX XXX.';
}

/// Turns the server's reason code into words a rep can say to a customer.
String friendlyReason(String? reason) {
  switch (reason) {
    case 'rejected':
      return 'The customer declined the payment on their phone. Ask them, then try again.';
    case 'expired':
      return 'The customer did not approve in time. Try again when they are ready.';
    case 'payer_not_found':
      return 'This number has no Mobile Money account. Check the number with the customer.';
    default:
      return 'The payment did not go through. No money was taken. Try again or choose another payment method.';
  }
}

/// A random version 4 UUID, used as the Idempotency-Key of one payment attempt.
String newIdempotencyKey() {
  final r = Random.secure();
  final b = List<int>.generate(16, (_) => r.nextInt(256));
  b[6] = (b[6] & 0x0f) | 0x40; // version 4
  b[8] = (b[8] & 0x3f) | 0x80; // variant
  final h = b.map((x) => x.toRadixString(16).padLeft(2, '0')).join();
  return '${h.substring(0, 8)}-${h.substring(8, 12)}-${h.substring(12, 16)}-${h.substring(16, 20)}-${h.substring(20)}';
}

/// 78610 -> "78,610 FCFA".
String fcfa(int amount) {
  final s = amount.toString();
  final out = StringBuffer();
  for (var i = 0; i < s.length; i++) {
    if (i > 0 && (s.length - i) % 3 == 0) out.write(',');
    out.write(s[i]);
  }
  return '$out FCFA';
}
