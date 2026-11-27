// What the server answered to POST /orders. This file is ready to use.
class OrderAnswer {
  const OrderAnswer(this.statusCode, this.body);

  final int statusCode;
  final Map<String, dynamic> body;

  // 201 Created. A server that sees the same Idempotency-Key again may answer
  // the first result again, sometimes with 200: both mean "the order exists".
  bool get isCreated => statusCode == 201 || statusCode == 200;

  // The practice API wraps the order in "order"; the Orders API (Backend B2) uses "data".
  Map<String, dynamic>? get order {
    final o = body['data'] ?? body['order'];
    return o is Map<String, dynamic> ? o : null;
  }

  String? get serverId => order?['id'] as String?;
  int? get serverTotalFcfa => order?['total_fcfa'] as int?;
  String? get serverStatus => order?['status'] as String?; // e.g. awaiting_payment
  String get message => (body['message'] as String?) ?? 'The server answered $statusCode.';
}
