// A small client for the Nkwen Traders course API. It is ready to use.
// In Chapter 8 ("Calling APIs from the App") you will write a better one:
// friendly errors, timeouts, filters and tests.
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/product.dart';

const nkwenApiBase = 'https://innovakickstarter.com/api/course/nkwen';

class NkwenApi {
  NkwenApi({http.Client? client, this.baseUrl = nkwenApiBase}) : _client = client ?? http.Client();

  final http.Client _client;
  final String baseUrl;

  // GET /products. The answer is {"count": 20, "currency": "FCFA", "products": [...]}.
  Future<List<Product>> fetchProducts() async {
    final response = await _client.get(Uri.parse('$baseUrl/products'));
    if (response.statusCode != 200) {
      throw Exception('The server answered ${response.statusCode}.');
    }
    final body = jsonDecode(response.body) as Map<String, dynamic>;
    final list = body['products'] as List<dynamic>;
    return list.map((json) => Product.fromJson(json as Map<String, dynamic>)).toList();
  }
}
