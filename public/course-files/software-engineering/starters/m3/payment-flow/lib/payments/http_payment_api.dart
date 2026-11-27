// For Chapter 10: the same PaymentApi, but talking to a REAL Nkwen server
// (your squad's or your Backend friend's B3 API). You do not need it in Chapter 9.
//
// Every call sends the staff login token: B3's  POST /api/login  answers
// {"token": "...", "token_type": "Bearer", "data": {...the user...}}.
// Without the header, B3 answers 401.
import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import 'payment_api.dart';

class HttpPaymentApi implements PaymentApi {
  HttpPaymentApi({required this.baseUrl, required this.token, http.Client? client, this.timeout = const Duration(seconds: 20)})
      : _client = client ?? http.Client();

  /// For example https://nkwen-squad-3.onrender.com/api
  final String baseUrl;

  /// The token from POST /api/login. Keep it in secure storage, never in Git.
  final String token;
  final Duration timeout;
  final http.Client _client;

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $token',
      };

  @override
  Future<PaymentStarted> startPayment({required String orderId, required String phone, required String idempotencyKey}) async {
    final digits = phone.replaceAll(RegExp(r'\D'), '');
    final body = jsonEncode({'phone': digits.startsWith('237') ? digits : '237$digits'});
    final response = await _send(() => _client.post(Uri.parse('$baseUrl/orders/$orderId/payments'),
        headers: {..._headers, 'Idempotency-Key': idempotencyKey}, body: body));
    final json = _json(response);
    if (response.statusCode != 202 && response.statusCode != 200) {
      throw PaymentRefused(response.statusCode, json['message'] as String? ?? 'The server refused the payment.');
    }
    final data = json['data'] as Map<String, dynamic>;
    return PaymentStarted(reference: data['reference'].toString(), state: PaymentState.pending);
  }

  @override
  Future<PaymentCheck> checkPayment({required String orderId, required String reference}) async {
    final response = await _send(() => _client.get(Uri.parse('$baseUrl/orders/$orderId'), headers: _headers));
    final json = _json(response);
    if (response.statusCode != 200) {
      throw PaymentRefused(response.statusCode, json['message'] as String? ?? 'Could not read the order.');
    }
    final status = (json['data'] as Map<String, dynamic>)['status'] as String;
    switch (status) {
      case 'paid':
      case 'delivered':
        return PaymentCheck(state: PaymentState.successful, orderStatus: status);
      case 'cancelled':
        return PaymentCheck(state: PaymentState.failed, orderStatus: status, reason: 'failed');
      default: // awaiting_payment: the customer has not approved yet
        return PaymentCheck(state: PaymentState.pending, orderStatus: status);
    }
  }

  Future<http.Response> _send(Future<http.Response> Function() request) async {
    try {
      return await request().timeout(timeout);
    } on TimeoutException {
      throw const NetworkException('The server did not answer in time.');
    } on http.ClientException {
      throw const NetworkException('No connection.');
    }
  }

  Map<String, dynamic> _json(http.Response response) {
    try {
      final decoded = jsonDecode(utf8.decode(response.bodyBytes));
      return decoded is Map<String, dynamic> ? decoded : {};
    } on FormatException {
      return {};
    }
  }
}
