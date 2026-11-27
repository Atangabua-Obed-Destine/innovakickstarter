// The client for the Nkwen Traders course API.
// Every problem becomes an ApiException with words a sales rep understands.
import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/branch.dart';
import '../models/product.dart';

const nkwenApiBase = 'https://innovakickstarter.com/api/course/nkwen';

// What went wrong, so screens can choose what to show (and whether to retry).
enum ApiProblem { offline, timeout, notFound, rateLimited, server, badData, other }

class ApiException implements Exception {
  const ApiException(this.problem, this.message, {this.statusCode});

  final ApiProblem problem;
  final String message; // words for the rep, never a stack trace
  final int? statusCode; // null when there was no answer at all

  // Trying again later can help with these problems.
  bool get canRetry =>
      problem == ApiProblem.offline ||
      problem == ApiProblem.timeout ||
      problem == ApiProblem.rateLimited ||
      problem == ApiProblem.server;

  @override
  String toString() => message;
}

class NkwenApi {
  NkwenApi({
    http.Client? client,
    this.baseUrl = nkwenApiBase,
    this.timeout = const Duration(seconds: 15),
    this.switches = const {},
  }) : _client = client ?? http.Client();

  final http.Client _client;
  final String baseUrl;
  final Duration timeout;
  // Teaching switches added to every request, e.g. {'delay': '1500'} or {'fail': '503'}.
  final Map<String, String> switches;

  Uri uri(String path, [Map<String, String> query = const {}]) {
    final all = {...switches, ...query};
    return Uri.parse('$baseUrl$path').replace(queryParameters: all.isEmpty ? null : all);
  }

  Future<List<Product>> fetchProducts({String? branch, String? category, String? search}) async {
    // TODO 2: build the query: add 'branch', 'category' and 'search' only when they are given.
    //         Skip a search that is empty or only spaces, and trim() it.
    //         Tip: a map literal can contain  if (branch != null) 'branch': branch,
    final query = <String, String>{};
    final body = await _getJson('/products', query);
    return _parse(() => (body['products'] as List)
        .map((json) => Product.fromJson(json as Map<String, dynamic>))
        .toList());
  }

  // TODO 3: GET /products/{id} and return one Product (use _getJson and _parse).
  Future<Product> fetchProduct(int id) async {
    throw UnimplementedError('TODO 3: fetchProduct');
  }

  // TODO 4: GET /branches. The answer is {"branches": [...]}. Return a list of Branch.
  Future<List<Branch>> fetchBranches() async {
    throw UnimplementedError('TODO 4: fetchBranches');
  }

  // GET a URL and give back the JSON object, or throw an ApiException.
  Future<Map<String, dynamic>> _getJson(String path, [Map<String, String> query = const {}]) async {
    // TODO 1: stop waiting after [timeout], and turn "no answer" into an ApiException:
    //   - add .timeout(timeout) after _client.get(...)
    //   - catch TimeoutException      -> ApiException(ApiProblem.timeout, '...words for the rep...')
    //   - catch http.ClientException  -> ApiException(ApiProblem.offline, '...words for the rep...')
    //     (ClientException is what the http package throws when there is no network.)
    final response = await _client.get(uri(path, query), headers: {'Accept': 'application/json'});
    if (response.statusCode != 200) {
      throw errorFor(response);
    }
    return decodeObject(response);
  }

  // Read the body as UTF-8 JSON. The server does not always name its charset.
  static Map<String, dynamic> decodeObject(http.Response response) {
    // TODO 5: decode utf8.decode(response.bodyBytes) (not response.body), then jsonDecode it.
    //         Return it if it is a Map<String, dynamic>. If it is not JSON (FormatException)
    //         or not an object, throw ApiException(ApiProblem.badData, '...').
    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  // Turn an error status into words for the rep.
  static ApiException errorFor(http.Response response) {
    final code = response.statusCode;
    String? serverMessage;
    try {
      final body = jsonDecode(utf8.decode(response.bodyBytes));
      if (body is Map && body['message'] is String) serverMessage = body['message'] as String;
    } on FormatException {
      // not JSON: keep our own words
    }
    // TODO 6: choose the problem and the words. Always pass statusCode: code.
    //   404       -> ApiProblem.notFound (use serverMessage if there is one)
    //   429       -> ApiProblem.rateLimited ("wait a minute")
    //   500-599   -> ApiProblem.server (say it is the server, not the rep, and to try later)
    //   any other -> ApiProblem.other
    return ApiException(ApiProblem.other, 'Error $code', statusCode: code);
  }

  // A wrong shape (for example "products" is not a list) throws a TypeError: turn it into badData.
  T _parse<T>(T Function() read) {
    try {
      return read();
    } on TypeError {
      throw const ApiException(ApiProblem.badData, 'The server sent something the app cannot read.');
    }
  }
}
