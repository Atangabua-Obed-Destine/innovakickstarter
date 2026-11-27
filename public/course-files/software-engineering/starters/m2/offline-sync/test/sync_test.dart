// Self-check for "Offline-First Orders & Sync".
// Run it:  flutter test test/sync_test.dart
// A fake server plays the Orders API; the database is a real SQLite in memory.
// Do not change this file.
import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:nkwen_rep_app/data/order_store.dart';
import 'package:nkwen_rep_app/models/product.dart';
import 'package:nkwen_rep_app/models/saved_order.dart';
import 'package:nkwen_rep_app/services/nkwen_api.dart';
import 'package:nkwen_rep_app/services/orders_api.dart';
import 'package:nkwen_rep_app/state/order_draft.dart';
import 'package:nkwen_rep_app/sync/order_queue.dart';
import 'package:nkwen_rep_app/sync/sync_service.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';

import 'db_helpers.dart';
import 'helpers.dart';
import 'support/fake_orders_server.dart';

Product product(int id) =>
    Product.fromJson((productsFixture()['products'] as List).firstWhere((p) => p['id'] == id) as Map<String, dynamic>);

void main() {
  late Database db;
  late OrderStore store;
  late FakeOrdersServer server;
  late SyncService sync;

  var minute = 0;
  Future<SavedOrder> queue(Map<int, int> lines, {String branch = 'NKW', String name = 'Tamfu & Sons Store', String payment = 'cash'}) {
    final draft = OrderDraft()
      ..setBranch(branch)
      ..setPaymentMethod(payment);
    lines.forEach((id, qty) => draft.add(product(id), qty));
    minute++;
    return store.saveDraft(draft, customerName: name, now: DateTime(2026, 11, 26, 9, minute));
  }

  setUp(() async {
    db = await openTestDb();
    store = OrderStore(db);
    server = FakeOrdersServer();
    sync = SyncService(api: OrdersApi(client: server.client, baseUrl: 'https://api.test/nkwen'), store: store);
  });
  tearDown(() async => db.close());

  group('OrdersApi.postOrder', () {
    test('sends JSON with the Idempotency-Key, and gives back 201, 422 and 409 without throwing', () async {
      final api = OrdersApi(client: server.client, baseUrl: 'https://api.test/nkwen');
      final ok = await api.postOrder({'branch': 'CAV', 'customer_name': 'Mbah Clarisse', 'payment_method': 'mtn_momo', 'items': [{'product_id': 6, 'quantity': 2}, {'product_id': 15, 'quantity': 1}]}, idempotencyKey: 'key-1');
      final sent = server.requests.single;
      expect(sent.headers['Idempotency-Key'], 'key-1', reason: 'TODO 1: send the Idempotency-Key header.');
      expect(sent.headers['Content-Type'], startsWith('application/json'));
      expect(sent.url.path, '/nkwen/orders');
      expect(ok.statusCode, 201);
      expect(ok.serverTotalFcfa, 78610, reason: 'The same order on the real practice API also gives 78,610 FCFA.');
      final bad = await api.postOrder({'branch': 'NKW', 'customer_name': 'A', 'payment_method': 'cash', 'items': [{'product_id': 1, 'quantity': 1}]}, idempotencyKey: 'key-2');
      expect(bad.statusCode, 422, reason: 'TODO 3: a 422 is an answer, not an exception.');
      final short = await api.postOrder({'branch': 'NKW', 'customer_name': 'Ade', 'payment_method': 'cash', 'items': [{'product_id': 5, 'quantity': 10}]}, idempotencyKey: 'key-3');
      expect(short.statusCode, 409);
      expect(short.body['available'], 5);
    });

    test('no network throws an ApiException (offline)', () async {
      server.offline = true;
      final api = OrdersApi(client: server.client, baseUrl: 'https://api.test/nkwen');
      await expectLater(api.postOrder({'branch': 'NKW'}, idempotencyKey: 'k'),
          throwsA(isA<ApiException>().having((e) => e.problem, 'problem', ApiProblem.offline)), reason: 'TODO 2.');
    });
  });

  test('a key reused for a DIFFERENT order is refused (422), as on the practice API', () async {
    final api = OrdersApi(client: server.client, baseUrl: 'https://api.test/nkwen');
    final order = {'branch': 'NKW', 'customer_name': 'Ade', 'payment_method': 'cash', 'items': [{'product_id': 1, 'quantity': 2}]};
    final first = await api.postOrder(order, idempotencyKey: 'key-9');
    final again = await api.postOrder(order, idempotencyKey: 'key-9');
    expect(again.serverId, first.serverId, reason: 'Same key, same order: the first order again.');
    final other = await api.postOrder({...order, 'items': [{'product_id': 1, 'quantity': 3}]}, idempotencyKey: 'key-9');
    expect(other.statusCode, 422, reason: 'A new order needs a new key: never reuse a localId.');
    expect(server.savedOrders.length, 1);
  });

  test('a pending order is sent and marked synced with the server id and total', () async {
    final order = await queue({6: 2, 15: 1}, branch: 'CAV', payment: 'mtn_momo');
    final report = await sync.syncNow();
    expect(report.sent, 1, reason: 'TODO 9.');
    final after = (await store.find(order.localId))!;
    expect(after.syncStatus, SyncStatus.synced, reason: 'TODO 5: markSynced.');
    expect(after.serverId, 'ORD-TEST0001');
    expect(after.serverTotalFcfa, 78610);
    expect(after.serverStatus, 'awaiting_payment', reason: "Keep the server's order status: Chapter 9 takes the payment from it.");
    expect(jsonDecode(server.requests.single.body), order.toApiJson(), reason: 'Send order.toApiJson(): no prices.');
    expect(await store.pendingOrders(), isEmpty, reason: 'TODO 4: only pending orders are waiting.');
  });

  test('with no network the order stays on the phone, waiting', () async {
    final order = await queue({1: 4});
    server.offline = true;
    final report = await sync.syncNow();
    expect(report.failed, 1);
    final after = (await store.find(order.localId))!;
    expect(after.syncStatus, SyncStatus.pending);
    expect(after.attempts, 1, reason: 'TODO 7: count the attempt.');
    expect(after.lastError, isNotNull);
    expect(server.savedOrders, isEmpty);
  });

  test('NO DUPLICATES: the answer is lost, the phone tries again, the server creates it once', () async {
    final order = await queue({6: 1});
    server.loseNextAnswer = true; // the server creates the order, then the network drops
    await sync.syncNow();
    expect((await store.find(order.localId))!.syncStatus, SyncStatus.pending, reason: 'The phone never heard back, so it must keep the order.');
    await sync.syncNow(); // back online: send again
    expect(server.savedOrders.length, 1, reason: 'Send the SAME Idempotency-Key (the localId) every time.');
    expect(server.requests.map((r) => r.headers['Idempotency-Key']).toSet(), {order.localId});
    expect((await store.find(order.localId))!.serverId, 'ORD-TEST0001');
  });

  test('NO DUPLICATES: two syncs at the same time send each order once', () async {
    await queue({6: 1});
    await queue({9: 2});
    server.delay = const Duration(milliseconds: 50);
    final results = await Future.wait([sync.syncNow(), sync.syncNow()]);
    expect(server.requests.length, 2, reason: 'TODO 8: a second syncNow() while one is running must give back the same run.');
    expect(identical(results[0], results[1]), isTrue);
    expect(server.savedOrders.length, 2);
  });

  test('a synced order is never sent again', () async {
    await queue({6: 1});
    await sync.syncNow();
    await sync.syncNow();
    expect(server.requests.length, 1);
  });

  test('409: needs attention with the stock in the message, and is not retried', () async {
    final order = await queue({5: 10}); // Rice 25kg: Nkwen has only 5
    final report = await sync.syncNow();
    expect(report.needAttention, 1);
    final after = (await store.find(order.localId))!;
    expect(after.syncStatus, SyncStatus.needsAttention, reason: 'TODO 6.');
    expect(after.lastError, contains('5'), reason: 'Tell the rep how many the branch has.');
    await sync.syncNow();
    expect(server.requests.length, 1, reason: 'Sending the same order again would get the same 409.');
  });

  test('422: needs attention with the field message', () async {
    final order = await queue({1: 1}, name: 'A');
    await sync.syncNow();
    final after = (await store.find(order.localId))!;
    expect(after.syncStatus, SyncStatus.needsAttention);
    expect(after.lastError, contains('at least 2 characters'));
  });

  test('503: the run stops, the orders wait, and the next run sends them in order', () async {
    final first = await queue({6: 1});
    final second = await queue({9: 2});
    server.failNext = 1;
    final report = await sync.syncNow();
    expect(server.requests.length, 1, reason: 'Stop at a 503: do not hammer a server in trouble.');
    expect(report.failed, 1);
    expect((await store.find(first.localId))!.syncStatus, SyncStatus.pending);
    await sync.syncNow();
    final sentKeys = server.requests.skip(1).map((r) => r.headers['Idempotency-Key']).toList();
    expect(sentKeys, [first.localId, second.localId], reason: 'Oldest first.');
    expect(server.savedOrders.length, 2);
  });

  test('the practice API shape ({"order": ...}) is understood too', () async {
    server = FakeOrdersServer(practiceShape: true);
    sync = SyncService(api: OrdersApi(client: server.client, baseUrl: 'https://api.test/nkwen'), store: store);
    final order = await queue({13: 4});
    await sync.syncNow();
    expect((await store.find(order.localId))!.serverId, 'ORD-TEST0001');
  });
}
