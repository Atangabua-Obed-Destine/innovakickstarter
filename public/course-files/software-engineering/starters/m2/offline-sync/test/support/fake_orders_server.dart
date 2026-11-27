// A pretend Orders API for the tests. Do not change this file.
// It follows the Nkwen Orders API rules, like the practice API: the same Idempotency-Key
// with the same order gives back the first order again; the same key with a different
// order is refused with 422. Unlike the practice API, it can lose answers, fail or go
// offline on purpose, so the tests can prove "no duplicates".
import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import '../helpers.dart';

class FakeOrdersServer {
  FakeOrdersServer({this.practiceShape = false}) {
    for (final p in productsFixture()['products'] as List) {
      _products[p['id'] as int] = Map<String, dynamic>.from(p as Map);
    }
  }

  // true: answer like the practice API ({"order": ...}); false: like the Orders API ({"data": ...}).
  final bool practiceShape;

  final _products = <int, Map<String, dynamic>>{};
  final _answersByKey = <String, http.Response>{};
  final _bodyByKey = <String, String>{};

  final List<Map<String, dynamic>> savedOrders = []; // orders the server really created
  final List<http.Request> requests = []; // every POST it received

  bool offline = false; // no network at all
  int failNext = 0; // answer 503 this many times
  bool loseNextAnswer = false; // create the order, but the answer never reaches the phone
  Duration delay = Duration.zero;

  late final http.Client client = MockClient(_handle);

  // Change the stock of a product at a branch, as if another rep had sold some.
  void stockAt(int productId, String branch, int quantity) {
    (_products[productId]!['stock'] as Map)[branch] = quantity;
  }

  Future<http.Response> _handle(http.Request request) async {
    if (delay > Duration.zero) await Future.delayed(delay);
    if (offline) throw http.ClientException('Network is unreachable');
    requests.add(request);
    if (failNext > 0) {
      failNext--;
      return _json({'message': 'Service unavailable, try again later.'}, 503);
    }
    final key = request.headers['Idempotency-Key'];
    if (key != null && _answersByKey.containsKey(key)) {
      if (_bodyByKey[key] != request.body) {
        return _json({'message': 'This Idempotency-Key was already used for a different order.'}, 422);
      }
      return _answersByKey[key]!; // seen before: the first answer again, nothing new is created
    }
    final answer = _create(jsonDecode(request.body) as Map<String, dynamic>);
    if (key != null && answer.statusCode == 201) {
      _answersByKey[key] = answer; // only created orders are remembered, as on the practice API
      _bodyByKey[key] = request.body;
    }
    if (loseNextAnswer) {
      loseNextAnswer = false;
      throw http.ClientException('Connection closed before full header was received');
    }
    return answer;
  }

  http.Response _create(Map<String, dynamic> body) {
    final errors = <String, List<String>>{};
    const open = ['NKW', 'ML4', 'CAV', 'BAF'];
    if (!open.contains(body['branch'])) errors['branch'] = ['The branch must be one of the open branches: NKW, ML4, CAV, BAF.'];
    final name = (body['customer_name'] ?? '').toString();
    if (name.length < 2) errors['customer_name'] = ['The customer name field must be at least 2 characters.'];
    if (!['cash', 'mtn_momo', 'orange_money', 'bank_transfer'].contains(body['payment_method'])) {
      errors['payment_method'] = ['The selected payment method is invalid.'];
    }
    final items = (body['items'] as List?) ?? [];
    if (items.isEmpty) errors['items'] = ['The items field is required.'];
    for (var i = 0; i < items.length; i++) {
      final q = items[i]['quantity'];
      if (q is! int || q < 1) errors['items.$i.quantity'] = ['The items.$i.quantity field must be at least 1.'];
      if (body.containsKey('price_fcfa') || (items[i] as Map).containsKey('unit_price_fcfa')) {
        errors['items.$i'] = ['Do not send prices: the server sets them.'];
      }
    }
    if (errors.isNotEmpty) return _json({'message': 'The order is not valid.', 'errors': errors}, 422);
    final lines = <Map<String, dynamic>>[];
    for (final item in items) {
      final p = _products[item['product_id']];
      if (p == null) return _json({'message': 'The order is not valid.', 'errors': {'items.0.product_id': ['No product with id ${item['product_id']}.']}}, 422);
      final available = (p['stock'] as Map)[body['branch']] as int? ?? 0;
      if ((item['quantity'] as int) > available) {
        return _json({'message': 'Not enough ${p['name']} at this branch.', 'product_id': p['id'], 'requested': item['quantity'], 'available': available}, 409);
      }
      lines.add({'product_id': p['id'], 'name': p['name'], 'quantity': item['quantity'], 'unit_price_fcfa': p['price_fcfa'], 'line_total_fcfa': (p['price_fcfa'] as int) * (item['quantity'] as int)});
    }
    final order = {
      'id': 'ORD-TEST${(savedOrders.length + 1).toString().padLeft(4, '0')}',
      'branch': body['branch'],
      'customer_name': name,
      'payment_method': body['payment_method'],
      'status': body['payment_method'] == 'cash' ? 'pay_at_counter' : 'awaiting_payment',
      'items': lines,
      'total_fcfa': lines.fold<int>(0, (s, l) => s + (l['line_total_fcfa'] as int)),
      'currency': 'FCFA',
    };
    savedOrders.add(order);
    return _json(practiceShape ? {'order': order, 'message': 'Order checked and priced.'} : {'data': order}, 201);
  }

  http.Response _json(Object body, int status) =>
      http.Response(jsonEncode(body), status, headers: {'content-type': 'application/json'});
}
