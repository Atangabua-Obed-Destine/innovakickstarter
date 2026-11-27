// Chapter 9 project self-checks, run INSIDE your rep app (from Chapter 8).
// They use your app's own test helpers (db_helpers.dart, helpers.dart, support/).
// Do not change them. Run:  flutter test test/order_detail_test.dart
import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:material_ui/material_ui.dart';
import 'package:nkwen_rep_app/data/order_extras.dart';
import 'package:nkwen_rep_app/data/order_store.dart';
import 'package:nkwen_rep_app/models/product.dart';
import 'package:nkwen_rep_app/models/saved_order.dart';
import 'package:nkwen_rep_app/payments/payment_flow.dart';
import 'package:nkwen_rep_app/payments/sandbox_payment_api.dart';
import 'package:nkwen_rep_app/screens/order_detail_screen.dart';
import 'package:nkwen_rep_app/services/order_status_api.dart';
import 'package:nkwen_rep_app/state/order_draft.dart';
import 'package:nkwen_rep_app/sync/order_queue.dart';
import 'package:provider/provider.dart';

import 'db_helpers.dart';
import 'helpers.dart';
import 'support/test_app.dart';

SavedOrder order({SyncStatus sync = SyncStatus.synced, String? serverId = 'ORD-7Q2K9X1B', int? total = 78610, String? status = 'awaiting_payment'}) =>
    SavedOrder(
      localId: 'local-1',
      branch: 'CAV',
      customerName: 'Mbah Clarisse',
      customerPhone: '6XXXXXXXX',
      paymentMethod: 'mtn_momo',
      items: const [SavedItem(productId: 6, name: 'Rice 50kg', quantity: 2, unitPriceFcfa: 36005)],
      estimatedTotalFcfa: 72010,
      createdAt: DateTime(2026, 12, 3, 10, 15),
      syncStatus: sync,
      serverId: serverId,
      serverTotalFcfa: total,
      serverStatus: status,
    );

List<Product> nkwenProducts() =>
    (productsFixture()['products'] as List).map((j) => Product.fromJson(j as Map<String, dynamic>)).toList();

/// Saves a real order in the test database, as sync would after a 201.
Future<SavedOrder> savedSentOrder(OrderStore store, {String status = 'awaiting_payment'}) async {
  final draft = OrderDraft()
    ..add(nkwenProducts().firstWhere((p) => p.name == 'Rice 50kg'), 2)
    ..setPaymentMethod('mtn_momo');
  final saved = await store.saveDraft(draft, customerName: 'Mbah Clarisse');
  await store.markSynced(saved.localId, serverId: 'ORD-7Q2K9X1B', serverTotalFcfa: 72010, serverStatus: status);
  return (await store.find(saved.localId))!;
}

Future<OrderStore> showDetail(WidgetTester tester, Widget Function(SavedOrder o) screen, {String status = 'awaiting_payment'}) async {
  final db = await openTestDb(noIsolate: true);
  addTearDown(db.close);
  final store = OrderStore(db);
  final saved = await savedSentOrder(store, status: status);
  await tester.pumpWidget(Provider<OrderStore>.value(value: store, child: MaterialApp(home: screen(saved))));
  return store;
}

PaymentFlow instantFlow(SavedOrder o) => PaymentFlow(
    api: SandboxPaymentApi(orders: {o.serverId!: o.serverTotalFcfa!}), orderId: o.serverId!, totalFcfa: o.serverTotalFcfa!, wait: (_) async {});

void main() {
  test('only a sent, priced order waiting for mobile money can be paid', () {
    expect(canTakePayment(order()), isTrue);
    expect(canTakePayment(order(status: 'pay_at_counter')), isFalse, reason: 'Cash orders are paid at the counter.');
    expect(canTakePayment(order(status: 'paid')), isFalse, reason: 'Never ask twice.');
    expect(canTakePayment(order(sync: SyncStatus.pending, serverId: null, total: null, status: null)), isFalse,
        reason: 'An unsent order has no server id: the server must know the order first.');
    expect(canTakePayment(order(total: null)), isFalse, reason: 'The amount comes from the server, never from the phone.');
  });

  testWidgets('pay from the order: PAID is saved only after the server confirms', (tester) async {
    final store = await showDetail(tester, (o) => OrderDetailScreen(order: o, makeFlow: instantFlow));
    expect(find.byKey(const ValueKey('take-payment')), findsOneWidget);

    await tester.tap(find.byKey(const ValueKey('take-payment')));
    await tester.pumpAndSettle();
    expect(find.byKey(const Key('phone')), findsOneWidget);
    expect(tester.widget<TextField>(find.byKey(const Key('phone'))).controller!.text, isEmpty,
        reason: 'Do not prefill the number: the sandbox needs a MoMo test number.');
    await tester.enterText(find.byKey(const Key('phone')), '46733123456');
    await tester.tap(find.byKey(const Key('pay')));
    await tester.pumpAndSettle();
    expect(find.text('PAID'), findsOneWidget);
    await tester.pageBack();
    await tester.pumpAndSettle();

    expect((await store.allOrders()).single.serverStatus, 'paid');
    expect(find.byKey(const ValueKey('take-payment')), findsNothing);
  });

  testWidgets('a declined payment changes nothing on the phone', (tester) async {
    final store = await showDetail(tester, (o) => OrderDetailScreen(order: o, makeFlow: instantFlow));
    await tester.tap(find.byKey(const ValueKey('take-payment')));
    await tester.pumpAndSettle();
    await tester.enterText(find.byKey(const Key('phone')), '46733123451');
    await tester.tap(find.byKey(const Key('pay')));
    await tester.pumpAndSettle();
    await tester.pageBack();
    await tester.pumpAndSettle();
    expect((await store.allOrders()).single.serverStatus, 'awaiting_payment');
  });

  testWidgets('a receipt photo under 500 KB is saved with the order', (tester) async {
    final store = await showDetail(tester,
        (o) => OrderDetailScreen(order: o, takePhoto: () async => '/photos/a.jpg', photoBytes: (_) async => 180 * 1024));
    await tester.tap(find.byKey(const ValueKey('add-receipt')));
    await tester.pumpAndSettle();
    final saved = (await store.allOrders()).single;
    expect(await store.receiptFileNameFor(saved.localId), startsWith('receipt_ORD-7Q2K9X1B_'));
  });

  testWidgets('a status refresh with the Bearer token saves "delivered" and cancels reminders', (tester) async {
    final cancelled = <String>[];
    final seen = <http.Request>[];
    final api = OrderStatusApi(
      baseUrl: 'https://api.test/api',
      token: 'tok-123',
      client: MockClient((request) async {
        seen.add(request);
        return http.Response(jsonEncode({'data': {'id': 'ORD-7Q2K9X1B', 'status': 'delivered'}}), 200);
      }),
    );
    final store = await showDetail(tester,
        (o) => OrderDetailScreen(order: o, statusApi: api, cancelReminders: (id) async => cancelled.add(id)));
    await tester.tap(find.byKey(const ValueKey('refresh-status')));
    await tester.pumpAndSettle();

    expect(seen.single.headers['Authorization'], 'Bearer tok-123');
    expect((await store.allOrders()).single.serverStatus, 'delivered');
    expect(cancelled, ['ORD-7Q2K9X1B']);
    expect(find.text('Status: delivered'), findsOneWidget);
  });

  testWidgets('in the Outbox, tapping a sent order opens it', (tester) async {
    final app = await startTestApp(tester);
    await savedSentOrder(app.store);
    await tester.tap(find.byTooltip('Outbox'));
    await tester.pumpAndSettle();
    await tester.tap(find.textContaining('ORD-7Q2K9X1B'));
    await tester.pumpAndSettle();
    expect(find.byType(OrderDetailScreen), findsOneWidget, reason: 'Add onTap to the synced tile in outbox_screen.dart.');
    expect(find.byKey(const ValueKey('take-payment')), findsOneWidget);
  });
}
