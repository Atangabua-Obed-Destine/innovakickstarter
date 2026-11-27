import 'payment_api.dart';

/// A pretend Nkwen server with a pretend MoMo behind it. No real money moves.
///
/// It copies the MTN MoMo sandbox's test numbers (MoMo developer docs,
/// "Sandbox Use Cases"), which you met in Chapter 5:
///   46733123450 failed     46733123451 rejected   46733123452 expired
///   46733123453 ongoing (never finishes)          46733123454 delayed (slow)
///   46733123455 payer not found
///   46733123456 to 46733123459: the customer approves (success).
/// In this sandbox the "customer" answers after the app has checked
/// [checksBeforeAnswer] times.
class SandboxPaymentApi implements PaymentApi {
  SandboxPaymentApi({
    Map<String, int>? orders,
    this.checksBeforeAnswer = 2,
    this.latency = Duration.zero,
  }) : _orderTotals = orders ?? {'ORD-7Q2K9X1B': 78610} {
    for (final id in _orderTotals.keys) {
      orderStatus[id] = 'awaiting_payment';
    }
  }

  final Map<String, int> _orderTotals;
  final int checksBeforeAnswer;
  final Duration latency;

  /// The order statuses on the "server". Only the server changes them.
  final Map<String, String> orderStatus = {};

  // --- Switches for practising bad networks ---
  /// The next N start requests never reach the server.
  int dropNextStarts = 0;

  /// The next N start requests reach the server, but the answer is lost.
  int loseNextStartAnswers = 0;

  /// The next N status checks fail with a NetworkException.
  int dropNextChecks = 0;

  // --- What happened (tests read these) ---
  int startCalls = 0;
  int checkCalls = 0;
  final List<String> keysSeen = [];
  final Map<String, _SandboxPayment> _byKey = {};

  /// How many different payments the customer was asked to approve.
  int get paymentsCreated => _byKey.length;

  @override
  Future<PaymentStarted> startPayment({
    required String orderId,
    required String phone,
    required String idempotencyKey,
  }) async {
    startCalls++;
    await Future<void>.delayed(latency);
    if (dropNextStarts > 0) {
      dropNextStarts--;
      throw const NetworkException('The request never reached the server.');
    }
    keysSeen.add(idempotencyKey);
    final status = orderStatus[orderId];
    if (status == null) throw PaymentRefused(404, 'No order with id $orderId.');

    final existing = _byKey[idempotencyKey];
    final payment = existing ?? _create(orderId, phone, status, idempotencyKey);

    if (loseNextStartAnswers > 0) {
      loseNextStartAnswers--;
      throw const NetworkException('The server got the request, but its answer was lost.');
    }
    return PaymentStarted(reference: payment.reference, state: PaymentState.pending);
  }

  _SandboxPayment _create(String orderId, String phone, String status, String key) {
    if (status == 'paid' || status == 'delivered') {
      throw const PaymentRefused(409, 'This order is already paid.');
    }
    if (!RegExp(r'^4673312345\d$').hasMatch(phone)) {
      throw const PaymentRefused(422, 'The sandbox only accepts test numbers 46733123450 to 46733123459.');
    }
    final payment = _SandboxPayment('PAY-${_byKey.length + 1}', orderId, phone);
    _byKey[key] = payment;
    return payment;
  }

  @override
  Future<PaymentCheck> checkPayment({required String orderId, required String reference}) async {
    checkCalls++;
    await Future<void>.delayed(latency);
    if (dropNextChecks > 0) {
      dropNextChecks--;
      throw const NetworkException();
    }
    final payment = _byKey.values.where((p) => p.reference == reference && p.orderId == orderId).firstOrNull;
    if (payment == null) throw PaymentRefused(404, 'No payment $reference for order $orderId.');

    payment.checks++;
    final ending = payment.phone.substring(payment.phone.length - 1);
    final needed = ending == '4' ? checksBeforeAnswer + 4 : checksBeforeAnswer;
    if (ending == '3' || payment.checks < needed) {
      return PaymentCheck(state: PaymentState.pending, orderStatus: orderStatus[orderId]!);
    }
    const reasons = {'0': 'failed', '1': 'rejected', '2': 'expired', '5': 'payer_not_found'};
    final reason = reasons[ending];
    if (reason != null) {
      return PaymentCheck(state: PaymentState.failed, orderStatus: orderStatus[orderId]!, reason: reason);
    }
    orderStatus[orderId] = 'paid'; // the server marks the order paid, never the app
    return PaymentCheck(state: PaymentState.successful, orderStatus: 'paid');
  }
}

class _SandboxPayment {
  _SandboxPayment(this.reference, this.orderId, this.phone);
  final String reference;
  final String orderId;
  final String phone;
  int checks = 0;
}
