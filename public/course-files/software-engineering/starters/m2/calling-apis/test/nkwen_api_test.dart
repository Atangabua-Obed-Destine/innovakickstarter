// Self-check for "Calling APIs from the App": the API client, with no screens.
// Run it:  flutter test test/nkwen_api_test.dart
// A MockClient plays the server, so these tests need no internet. Do not change this file.
import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:nkwen_rep_app/services/nkwen_api.dart';

import 'helpers.dart';

const jsonHeaders = {'content-type': 'application/json'}; // no charset, like the real server

NkwenApi apiAnswering(Future<http.Response> Function(http.Request r) answer,
        {Duration timeout = const Duration(seconds: 15), Map<String, String> switches = const {}}) =>
    NkwenApi(baseUrl: 'https://api.test/nkwen', client: MockClient(answer), timeout: timeout, switches: switches);

Future<ApiException> problemOf(Future<Object?> call) async {
  try {
    await call;
  } on ApiException catch (e) {
    return e;
  }
  fail('Expected an ApiException, but the call did not throw one.');
}

void expectFriendly(ApiException e) {
  expect(e.message, isNotEmpty);
  for (final word in ['Exception', 'SocketException', 'TimeoutException', 'FormatException', 'TypeError', 'null']) {
    expect(e.message.contains(word), isFalse, reason: 'A rep should never read "$word". Write plain words.');
  }
}

void main() {
  test('fetchProducts reads all 20 products', () async {
    final api = apiAnswering((r) async => http.Response(jsonEncode(productsFixture()), 200, headers: jsonHeaders));
    final products = await api.fetchProducts();
    expect(products.length, 20);
    expect(products.firstWhere((p) => p.id == 6).priceFcfa, 36005);
  });

  test('filters are sent as query parameters, and an empty search is left out', () async {
    late Uri asked;
    final api = apiAnswering((r) async {
      asked = r.url;
      return http.Response('{"count":0,"currency":"FCFA","products":[]}', 200, headers: jsonHeaders);
    });
    await api.fetchProducts(branch: 'ML4', search: '  rice ');
    expect(asked.path, '/nkwen/products');
    expect(asked.queryParameters, {'branch': 'ML4', 'search': 'rice'}, reason: 'TODO 2: add the filters, trim the search.');
    await api.fetchProducts(search: '   ');
    expect(asked.queryParameters.containsKey('search'), isFalse, reason: 'TODO 2: skip an empty search.');
  });

  test('teaching switches are added to every request', () async {
    late Uri asked;
    final api = apiAnswering((r) async {
      asked = r.url;
      return http.Response('{"branches":[]}', 200, headers: jsonHeaders);
    }, switches: {'delay': '1500'});
    await api.fetchBranches();
    expect(asked.queryParameters['delay'], '1500');
  });

  test('fetchBranches reads the five branches; Limbe is not open', () async {
    final api = apiAnswering((r) async => http.Response(
        '{"branches":[{"code":"NKW","name":"Nkwen","city":"Bamenda","region":"North-West","manager":"Mrs. Enow Patience","opened_year":2019,"status":"open"},'
        '{"code":"LMB","name":"Limbe Down Beach","city":"Limbe","region":"South-West","manager":"Mr. Ekane Joseph","opened_year":2027,"status":"planned"}]}',
        200,
        headers: jsonHeaders));
    final branches = await api.fetchBranches();
    expect(branches.map((b) => b.code), ['NKW', 'LMB'], reason: 'TODO 4 and TODO 7.');
    expect(branches.last.isOpen, isFalse);
    expect(branches.first.openedYear, 2019);
  });

  test('names with accents are read as UTF-8', () async {
    final body = utf8.encode('{"id":21,"sku":"X","name":"Café Touba 250g","category":"Groceries","unit":"bag","price_fcfa":1200,"stock":{"NKW":4}}');
    final api = apiAnswering((r) async => http.Response.bytes(body, 200, headers: jsonHeaders));
    final product = await api.fetchProduct(21);
    expect(product.name, 'Café Touba 250g', reason: 'TODO 5: decode utf8.decode(response.bodyBytes).');
  });

  test('404 gives notFound with the server\'s own message', () async {
    final api = apiAnswering((r) async => http.Response('{"message":"No product with id 999."}', 404, headers: jsonHeaders));
    final e = await problemOf(api.fetchProduct(999));
    expect(e.problem, ApiProblem.notFound, reason: 'TODO 6.');
    expect(e.statusCode, 404);
    expect(e.message, 'No product with id 999.');
    expect(e.canRetry, isFalse, reason: 'Asking again will not create the product.');
  });

  test('503 gives a server problem that can be retried', () async {
    final api = apiAnswering((r) async => http.Response('{"message":"Service unavailable"}', 503, headers: jsonHeaders));
    final e = await problemOf(api.fetchProducts());
    expect(e.problem, ApiProblem.server, reason: 'TODO 6: 500-599 is a server problem.');
    expect(e.canRetry, isTrue);
    expectFriendly(e);
  });

  test('429 gives rateLimited', () async {
    final api = apiAnswering((r) async => http.Response('{"message":"Too Many Attempts."}', 429, headers: jsonHeaders));
    final e = await problemOf(api.fetchProducts());
    expect(e.problem, ApiProblem.rateLimited);
    expectFriendly(e);
  });

  test('no network gives offline', () async {
    final api = apiAnswering((r) async => throw http.ClientException('Failed host lookup'));
    final e = await problemOf(api.fetchProducts());
    expect(e.problem, ApiProblem.offline, reason: 'TODO 1: catch http.ClientException.');
    expect(e.statusCode, isNull);
    expectFriendly(e);
  });

  test('a server that never answers gives timeout', () async {
    final api = apiAnswering((r) async {
      await Future.delayed(const Duration(milliseconds: 400));
      return http.Response('{"count":0,"products":[]}', 200, headers: jsonHeaders);
    }, timeout: const Duration(milliseconds: 100));
    final e = await problemOf(api.fetchProducts());
    expect(e.problem, ApiProblem.timeout, reason: 'TODO 1: add .timeout(timeout) and catch TimeoutException.');
    expectFriendly(e);
  });

  test('an HTML page or a wrong shape gives badData', () async {
    final html = apiAnswering((r) async => http.Response('<html>Wi-Fi login</html>', 200, headers: {'content-type': 'text/html'}));
    expect((await problemOf(html.fetchProducts())).problem, ApiProblem.badData, reason: 'TODO 5: catch FormatException.');
    final wrong = apiAnswering((r) async => http.Response('{"products":"oops"}', 200, headers: jsonHeaders));
    expect((await problemOf(wrong.fetchProducts())).problem, ApiProblem.badData);
  });
}
