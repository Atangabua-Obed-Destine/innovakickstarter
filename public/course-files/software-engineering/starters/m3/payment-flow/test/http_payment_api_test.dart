// Checks the Chapter 10 client against a pretend B3 server. These already pass:
// read them to see what a real server conversation looks like.
import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:nkwen_payments/payments/http_payment_api.dart';
import 'package:nkwen_payments/payments/payment_api.dart';
import 'package:nkwen_payments/payments/payment_flow.dart';

void main() {
  test('every call sends the Bearer token; the payment starts with 202', () async {
    final seen = <http.Request>[];
    var status = 'awaiting_payment';
    final client = MockClient((request) async {
      seen.add(request);
      if (request.headers['Authorization'] != 'Bearer tok-123') {
        return http.Response(jsonEncode({'message': 'Unauthenticated.'}), 401);
      }
      if (request.method == 'POST') {
        status = 'paid'; // pretend the customer approved and MoMo called the server back
        return http.Response(jsonEncode({'data': {'reference': 'PAY-1', 'status': 'pending'}}), 202);
      }
      return http.Response(jsonEncode({'data': {'id': 'ORD-7Q2K9X1B', 'status': status}}), 200);
    });
    final api = HttpPaymentApi(baseUrl: 'https://api.test/api', token: 'tok-123', client: client);
    final flow = PaymentFlow(api: api, orderId: 'ORD-7Q2K9X1B', totalFcfa: 78610, sandbox: false, wait: (_) async {});

    await flow.pay('670 00 00 00');

    expect(flow.step, PayStep.paid);
    expect(seen.every((r) => r.headers['Authorization'] == 'Bearer tok-123'), isTrue);
    expect(seen.first.url.path, '/api/orders/ORD-7Q2K9X1B/payments');
    expect(jsonDecode(seen.first.body), {'phone': '237670000000'});
    expect(seen.first.headers['Idempotency-Key'], isNotNull);
    expect(seen[1].url.path, '/api/orders/ORD-7Q2K9X1B');
  });

  test('a wrong or missing token is a clear 401, not "no network"', () async {
    final client = MockClient((request) async => http.Response(jsonEncode({'message': 'Unauthenticated.'}), 401));
    final api = HttpPaymentApi(baseUrl: 'https://api.test/api', token: 'old', client: client);
    expect(
      () => api.startPayment(orderId: 'ORD-1', phone: '670000000', idempotencyKey: 'k'),
      throwsA(isA<PaymentRefused>().having((e) => e.statusCode, 'statusCode', 401)),
    );
  });
}
