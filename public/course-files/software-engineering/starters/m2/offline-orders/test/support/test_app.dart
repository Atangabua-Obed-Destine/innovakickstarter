// Starts the whole app for the project self-check, with fakes. Do not change this file.
import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:nkwen_rep_app/app.dart';
import 'package:nkwen_rep_app/data/order_store.dart';
import 'package:nkwen_rep_app/data/product_cache.dart';
import 'package:nkwen_rep_app/models/product.dart';
import 'package:nkwen_rep_app/services/nkwen_api.dart';
import 'package:nkwen_rep_app/services/orders_api.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';

import '../db_helpers.dart';
import '../helpers.dart';
import 'fake_orders_server.dart';

class TestApp {
  TestApp(this.db, this.server);

  final Database db;
  final FakeOrdersServer server;
  bool productsOffline = false; // the products API has no network

  OrderStore get store => OrderStore(db);
  ProductCache get cache => ProductCache(db);
}

// The real app (NkwenRepApp) with a real database in memory, a fake products API
// and a fake orders server. Nothing leaves your laptop.
Future<TestApp> startTestApp(WidgetTester tester, {List<Product>? cached, DateTime? cachedAt, bool productsOffline = false}) async {
  final db = await openTestDb(noIsolate: true);
  addTearDown(db.close);
  final app = TestApp(db, FakeOrdersServer())..productsOffline = productsOffline;
  if (cached != null) await ProductCache(db).saveAll(cached, now: cachedAt);
  final api = NkwenApi(
    baseUrl: 'https://api.test/nkwen',
    client: MockClient((request) async {
      if (app.productsOffline) throw http.ClientException('Network is unreachable');
      return http.Response(jsonEncode(productsFixture()), 200, headers: {'content-type': 'application/json'});
    }),
  );
  await tester.pumpWidget(NkwenRepApp(
    api: api,
    ordersApi: OrdersApi(client: app.server.client, baseUrl: 'https://api.test/nkwen'),
    db: db,
    syncOnStart: false,
  ));
  await tester.pumpAndSettle();
  return app;
}
