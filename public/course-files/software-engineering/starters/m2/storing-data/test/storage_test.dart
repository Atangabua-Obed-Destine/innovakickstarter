// Self-check for "Storing Data on the Phone".
// Run it:  flutter test test/storage_test.dart
// The database is a real SQLite database, kept in memory for each test. Do not change this file.
import 'dart:io';

import 'package:flutter_test/flutter_test.dart';
import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import 'package:nkwen_rep_app/data/local_db.dart';
import 'package:nkwen_rep_app/data/order_store.dart';
import 'package:nkwen_rep_app/data/product_cache.dart';
import 'package:nkwen_rep_app/models/product.dart';
import 'package:nkwen_rep_app/models/saved_order.dart';
import 'package:nkwen_rep_app/screens/outbox_screen.dart';
import 'package:nkwen_rep_app/state/order_draft.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';

import 'db_helpers.dart';
import 'helpers.dart';

List<Product> fixtureProducts() => (productsFixture()['products'] as List)
    .map((j) => Product.fromJson(j as Map<String, dynamic>))
    .toList();

Product product(int id) => fixtureProducts().firstWhere((p) => p.id == id);

OrderDraft draftOf(Map<int, int> lines, {String branch = 'NKW', String payment = 'cash'}) {
  final draft = OrderDraft()
    ..setBranch(branch)
    ..setPaymentMethod(payment);
  lines.forEach((id, qty) => draft.add(product(id), qty));
  return draft;
}

void main() {
  late Database db;
  setUp(() async => db = await openTestDb());
  tearDown(() async => db.close());

  group('ProductCache', () {
    test('saves the list and reads back the same products, stock included', () async {
      final cache = ProductCache(db);
      await cache.saveAll(fixtureProducts(), now: DateTime(2026, 11, 26, 10, 42));
      final back = await cache.loadAll();
      expect(back.length, 20, reason: 'TODO 1 and TODO 2.');
      final rice = back.firstWhere((p) => p.id == 6);
      expect(rice.priceFcfa, 36005);
      expect(rice.stockAt('ML4'), 3, reason: 'TODO 4: store the stock map as JSON text (stock_json).');
      expect(rice.reorderLevel, 5);
      expect(await cache.savedAt(), DateTime(2026, 11, 26, 10, 42), reason: 'TODO 1 and TODO 3: remember when it was saved.');
    });

    test('saving again replaces the old list (no duplicates)', () async {
      final cache = ProductCache(db);
      await cache.saveAll(fixtureProducts(), now: DateTime(2026, 11, 25));
      await cache.saveAll(fixtureProducts().take(3).toList(), now: DateTime(2026, 11, 26));
      expect((await cache.loadAll()).length, 3, reason: 'Delete the old rows first, inside the same transaction.');
      expect(await cache.savedAt(), DateTime(2026, 11, 26));
    });

    test('an empty cache gives an empty list and no date', () async {
      final cache = ProductCache(db);
      expect(await cache.loadAll(), isEmpty);
      expect(await cache.savedAt(), isNull);
    });
  });

  group('OrderStore', () {
    test('saveDraft keeps the order and its lines, with a UUID and status pending', () async {
      final store = OrderStore(db);
      final saved = await store.saveDraft(draftOf({6: 2, 15: 1}, branch: 'CAV', payment: 'mtn_momo'),
          customerName: 'Mbah Clarisse Provisions', customerPhone: '600000101');
      expect(saved.localId, matches(RegExp(r'^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$')),
          reason: 'A version 4 UUID, made on the phone.');
      expect(saved.syncStatus, SyncStatus.pending);
      expect(saved.items.length, 2, reason: 'TODO 5: insert one order_items row per line.');
      expect(saved.items.first.name, 'Rice 50kg');
      expect(saved.estimatedTotalFcfa, 36005 * 2 + 6600);
      expect(saved.toApiJson(), {
        'branch': 'CAV',
        'customer_name': 'Mbah Clarisse Provisions',
        'payment_method': 'mtn_momo',
        'items': [
          {'product_id': 6, 'quantity': 2},
          {'product_id': 15, 'quantity': 1},
        ],
      });
    });

    test('every saved order gets its own id, and allOrders lists the newest first', () async {
      final store = OrderStore(db);
      final first = await store.saveDraft(draftOf({1: 4}), customerName: 'Tamfu & Sons Store', now: DateTime(2026, 11, 26, 9));
      final second = await store.saveDraft(draftOf({1: 4}), customerName: 'Tamfu & Sons Store', now: DateTime(2026, 11, 26, 11));
      expect(first.localId, isNot(second.localId));
      final all = await store.allOrders();
      expect(all.map((o) => o.localId), [second.localId, first.localId], reason: 'TODO 6: newest first.');
    });

    test('an empty draft is refused, and nothing is saved', () async {
      final store = OrderStore(db);
      await expectLater(store.saveDraft(OrderDraft(), customerName: 'Nobody'), throwsArgumentError);
      expect(await store.allOrders(), isEmpty);
    });

    test('the orders survive closing and opening the database file', () async {
      // A real file this time, like on the phone after the app is killed.
      final path = '${Directory.systemTemp.createTempSync('nkwen').path}/nkwen_rep_test.db';
      var fileDb = await LocalDb.open(factory: databaseFactoryFfi, path: path);
      await OrderStore(fileDb).saveDraft(draftOf({9: 3}), customerName: 'Ngwa Mini-Market');
      await fileDb.close();
      fileDb = await LocalDb.open(factory: databaseFactoryFfi, path: path);
      final again = await OrderStore(fileDb).allOrders();
      await fileDb.close();
      expect(again.single.customerName, 'Ngwa Mini-Market');
      expect(again.single.items.single.quantity, 3);
    });
  });

  testWidgets('the Outbox lists the saved orders', (tester) async {
    final widgetDb = await openTestDb(noIsolate: true);
    addTearDown(widgetDb.close);
    final store = OrderStore(widgetDb);
    await store.saveDraft(draftOf({6: 2, 15: 1}, branch: 'ML4'), customerName: 'Mami Siri Chop Bar');
    await tester.pumpWidget(Provider<OrderStore>.value(
      value: store,
      child: const MaterialApp(home: OutboxScreen()),
    ));
    await tester.pumpAndSettle();
    expect(find.text('Mami Siri Chop Bar'), findsOneWidget, reason: 'TODO 7 and TODO 8.');
    expect(find.text('3 items · Mile 4 · Waiting to send'), findsOneWidget);
    expect(find.text('78,610 FCFA'), findsOneWidget);
  });
}
