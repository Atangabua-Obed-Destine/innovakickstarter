// Sends orders to POST /orders.
import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import 'nkwen_api.dart';
import 'order_answer.dart';

class OrdersApi {
  OrdersApi({http.Client? client, this.baseUrl = nkwenApiBase, this.timeout = const Duration(seconds: 20)})
      : _client = client ?? http.Client();

  final http.Client _client;
  final String baseUrl;
  final Duration timeout;

  // Every answer (201, 422, 409, 503...) comes back as an OrderAnswer.
  // Only "no answer at all" (no network, timeout) throws an ApiException.
  // The same order must always be sent with the same idempotencyKey (its localId),
  // so a server that already has it does not create it twice.
  Future<OrderAnswer> postOrder(Map<String, dynamic> order, {required String idempotencyKey}) async {
    // TODO 1: POST jsonEncode(order) to '$baseUrl/orders' with three headers:
    //           'Content-Type': 'application/json', 'Accept': 'application/json',
    //           'Idempotency-Key': idempotencyKey
    //         and .timeout(timeout).
    // TODO 2: catch TimeoutException and http.ClientException, and throw an ApiException
    //         (timeout / offline) whose message tells the rep the order is KEPT.
    // TODO 3: decode the body as UTF-8 JSON (an empty map if it is not a JSON object)
    //         and return OrderAnswer(response.statusCode, body). Do NOT throw for 422 or 409.
    throw UnimplementedError('TODO: postOrder');
  }
}
