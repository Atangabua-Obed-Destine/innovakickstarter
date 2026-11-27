// Consumer contract test for the rep app (flutter test).
// Copy this file to mobile/test/order_contract_test.dart.
//
// It reads the agreed example answers from contract/examples/ (one folder up from mobile/)
// and checks that YOUR model classes understand them. If someone changes the contract and
// its examples without updating the app, this test fails in CI, before a rep's app breaks
// on the road.
//
// It expects Order.fromJson (with id, status, totalFcfa and items with lineTotalFcfa) and
// StockProblem.fromJson (with productId, requested and available). If your names differ,
// change the import and the names, but keep what each test checks.
import 'dart:convert';
import 'dart:io';

import 'package:flutter_test/flutter_test.dart';
import 'package:nkwen_rep_app/models/order.dart'; // TODO: your package name and the file with your models

Map<String, dynamic> example(String name) =>
    jsonDecode(File('../contract/examples/$name.json').readAsStringSync()) as Map<String, dynamic>;

void main() {
  test('Order.fromJson reads the agreed order', () {
    final json = example('order-created')['data'] as Map<String, dynamic>;
    final items = json['items'] as List<dynamic>;
    final order = Order.fromJson(json);
    expect(order.id, json['id']);
    expect(order.status, json['status']);
    expect(order.totalFcfa, json['total_fcfa']);
    expect(order.items.length, items.length);
    expect(order.items.first.lineTotalFcfa, (items.first as Map<String, dynamic>)['line_total_fcfa']);
  });

  test('the lines add up to the total the server sent', () {
    final order = Order.fromJson(example('order-created')['data'] as Map<String, dynamic>);
    final sum = order.items.fold<int>(0, (total, item) => total + item.lineTotalFcfa);
    expect(sum, order.totalFcfa);
  });

  test('a 409 answer tells the rep how many are left', () {
    final json = example('error-409');
    final problem = StockProblem.fromJson(json);
    expect(problem.productId, json['product_id']);
    expect(problem.requested, json['requested']);
    expect(problem.available, json['available']);
  });
}
