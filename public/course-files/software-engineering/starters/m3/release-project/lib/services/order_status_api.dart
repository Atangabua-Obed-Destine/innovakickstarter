// Asks a REAL Orders API (your squad's, or a B3 API) for an order's status:
//   GET /api/orders/{id}   with   Authorization: Bearer <token>
// The practice API at innovakickstarter.com saves no orders, so it has no such endpoint:
// until Chapter 10 the app uses the status it saved when the order was sent.
import 'dart:convert';

import 'package:http/http.dart' as http;

class OrderStatusApi {
  OrderStatusApi({required this.baseUrl, required this.token, http.Client? client}) : _client = client ?? http.Client();

  final String baseUrl; // for example https://nkwen-squad-3.onrender.com/api
  final String token; // from POST /api/login: {"token": "...", "data": {...}}
  final http.Client _client;

  /// The order's status (awaiting_payment, paid, delivered...), or null if the server could not say.
  Future<String?> statusOf(String serverId) async {
    try {
      final response = await _client.get(Uri.parse('$baseUrl/orders/$serverId'),
          headers: {'Accept': 'application/json', 'Authorization': 'Bearer $token'});
      if (response.statusCode != 200) return null; // 401: log in again; 404: unknown order
      final data = (jsonDecode(response.body) as Map<String, dynamic>)['data'] as Map<String, dynamic>;
      return data['status'] as String?;
    } on http.ClientException {
      return null;
    } on FormatException {
      return null;
    }
  }
}
