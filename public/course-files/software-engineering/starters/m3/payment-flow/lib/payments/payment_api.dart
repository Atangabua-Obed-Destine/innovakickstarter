/// The conversation between the rep's app and the NKWEN server about one payment.
/// The app never talks to MTN MoMo or Wave directly: the server holds the
/// secret keys, asks the customer to pay, and hears the result.

/// What the server says about a payment. Like MoMo's PENDING, SUCCESSFUL, FAILED.
enum PaymentState { pending, successful, failed }

/// The server's first answer: "202 Accepted, I asked the customer".
/// It is NOT the result. The customer has not approved anything yet.
class PaymentStarted {
  const PaymentStarted({required this.reference, required this.state});

  /// The payment's id on the server, used to ask about it later.
  final String reference;
  final PaymentState state;
}

/// The server's answer when the app asks "how is this payment going?".
class PaymentCheck {
  const PaymentCheck({required this.state, required this.orderStatus, this.reason});

  final PaymentState state;

  /// The order's status in the Orders API: awaiting_payment, paid, delivered...
  final String orderStatus;

  /// Why a payment failed: rejected, expired, failed or payer_not_found.
  final String? reason;
}

/// The phone could not reach the server, or the answer was lost on the way.
/// We do NOT know if the server received the request.
class NetworkException implements Exception {
  const NetworkException([this.message = 'No answer from the server.']);
  final String message;
  @override
  String toString() => 'NetworkException: $message';
}

/// The server answered with an error, for example 409 (already paid) or 422.
class PaymentRefused implements Exception {
  const PaymentRefused(this.statusCode, this.message);
  final int statusCode;
  final String message;
  @override
  String toString() => 'PaymentRefused($statusCode): $message';
}

abstract class PaymentApi {
  /// POST /api/orders/{orderId}/payments  {"phone": "2376XXXXXXXX"}
  /// with the headers  Authorization: Bearer <token>  and  Idempotency-Key: <key>
  /// The server answers 202 Accepted and asks the customer to approve on their phone.
  /// Sending the SAME key again returns the SAME payment: nobody is asked twice.
  Future<PaymentStarted> startPayment({
    required String orderId,
    required String phone,
    required String idempotencyKey,
  });

  /// GET /api/orders/{orderId}  (with the same Bearer token): is the order paid yet?
  /// The server learns the result from MoMo's callback, or by asking MoMo itself.
  Future<PaymentCheck> checkPayment({required String orderId, required String reference});
}
