// Self-check for "Project: Offline Order Taking". The whole app, end to end.
// Run every test:  flutter test
// Then test on a real phone in airplane mode (see the lesson). Do not change this file.
import 'package:flutter_test/flutter_test.dart';
import 'package:material_ui/material_ui.dart';
import 'package:nkwen_rep_app/models/product.dart';
import 'package:nkwen_rep_app/models/saved_order.dart';
import 'package:nkwen_rep_app/widgets/basket_button.dart';
import 'package:nkwen_rep_app/widgets/product_card.dart';

import 'helpers.dart';
import 'support/test_app.dart';

List<Product> fixtureProducts() =>
    (productsFixture()['products'] as List).map((j) => Product.fromJson(j as Map<String, dynamic>)).toList();

// Product -> More (quantity - 1 times) -> Add to order.
Future<void> addToOrder(WidgetTester tester, String name, int quantity) async {
  await tester.scrollUntilVisible(find.text(name), 200, scrollable: find.ancestor(of: find.byType(ProductCard).first, matching: find.byType(Scrollable)).first);
  await tester.ensureVisible(find.text(name));
  await tester.pumpAndSettle();
  await tester.tap(find.text(name));
  await tester.pumpAndSettle();
  for (var i = 1; i < quantity; i++) {
    await tester.tap(find.byTooltip('More'));
    await tester.pump();
  }
  await tester.tap(find.text('Add to order'));
  await tester.pumpAndSettle();
}

// Basket -> Checkout -> name -> Save order. Ends back on the order draft.
Future<void> checkout(WidgetTester tester, String customerName, {bool doubleTap = false}) async {
  await tester.tap(find.byType(BasketButton));
  await tester.pumpAndSettle();
  expect(find.byKey(const ValueKey('checkout')), findsOneWidget, reason: "Add a Checkout button with key ValueKey('checkout') to the order draft.");
  await tester.tap(find.byKey(const ValueKey('checkout')));
  await tester.pumpAndSettle();
  await tester.enterText(find.byKey(const ValueKey('customer-name')), customerName);
  await tester.ensureVisible(find.byKey(const ValueKey('save-order')));
  await tester.tap(find.byKey(const ValueKey('save-order')));
  if (doubleTap) await tester.tap(find.byKey(const ValueKey('save-order')), warnIfMissed: false);
  await tester.pumpAndSettle();
}

Future<void> openOutbox(WidgetTester tester) async {
  while (find.byTooltip('Outbox').evaluate().isEmpty) {
    await tester.pageBack();
    await tester.pumpAndSettle();
  }
  await tester.tap(find.byTooltip('Outbox'));
  await tester.pumpAndSettle();
}

Future<void> sendNow(WidgetTester tester) async {
  await tester.tap(find.byTooltip('Send now'));
  await tester.pumpAndSettle();
}

void main() {
  testWidgets('with no network, the list shows the saved products and says it is offline', (tester) async {
    // The copy the phone saved yesterday at 17:05. Today there is no network.
    await startTestApp(tester, cached: fixtureProducts(), cachedAt: DateTime(2026, 11, 25, 17, 5), productsOffline: true);
    expect(find.byType(ProductCard), findsWidgets, reason: 'With no network, show the products saved in ProductCache.');
    expect(find.textContaining('Offline'), findsOneWidget, reason: 'Tell the rep the list is not live.');
    expect(find.textContaining('25/11'), findsOneWidget, reason: 'Say when the saved list is from.');
  });

  testWidgets('a good answer is saved on the phone for next time', (tester) async {
    final app = await startTestApp(tester);
    expect((await app.cache.loadAll()).length, 20, reason: 'After a successful download, keep a copy (ProductCache.saveAll).');
  });

  testWidgets('take an order with no network, then send it when the network is back', (tester) async {
    final app = await startTestApp(tester);
    app.server.offline = true;
    await addToOrder(tester, 'Rice 50kg', 2);
    await checkout(tester, 'Tamfu & Sons Store');
    expect(find.textContaining('saved on this phone'), findsOneWidget, reason: 'Tell the rep the order is safe on the phone.');
    expect(find.text('No items yet. Add products from the list.'), findsOneWidget, reason: 'Clear the draft after saving.');
    final saved = await app.store.allOrders();
    expect(saved.single.syncStatus, SyncStatus.pending);
    await openOutbox(tester);
    expect(find.textContaining('Waiting to send'), findsOneWidget);
    app.server.offline = false;
    await sendNow(tester);
    expect(find.textContaining('Sent · ORD-TEST0001'), findsOneWidget, reason: 'After sending, show the server id.');
    expect(app.server.savedOrders.length, 1);
  });

  testWidgets('no duplicates: a lost answer and two taps on "Send now" create one order', (tester) async {
    final app = await startTestApp(tester);
    app.server.offline = true;
    await addToOrder(tester, 'Palm Oil 5L', 3);
    await checkout(tester, 'Kamga Alimentation');
    await openOutbox(tester);
    app.server
      ..offline = false
      ..loseNextAnswer = true;
    await sendNow(tester); // created on the server, answer lost
    await tester.tap(find.byTooltip('Send now'));
    await tester.tap(find.byTooltip('Send now'), warnIfMissed: false);
    await tester.pumpAndSettle();
    expect(app.server.savedOrders.length, 1, reason: 'Same Idempotency-Key every time; one sync at a time.');
    expect(find.textContaining('Sent · ORD-TEST0001'), findsOneWidget);
  });

  testWidgets('a double tap on "Save order" saves one order', (tester) async {
    final app = await startTestApp(tester);
    app.server.offline = true;
    await addToOrder(tester, 'Sugar 1kg', 5);
    await checkout(tester, 'Ngwa Mini-Market', doubleTap: true);
    expect((await app.store.allOrders()).length, 1);
  });

  testWidgets('a refused order (409) says what is wrong and is not sent again', (tester) async {
    final app = await startTestApp(tester);
    app.server.offline = true;
    // Rice 25kg: the list shows 5 at Nkwen. Add 5, then the stock changes before we sync.
    await addToOrder(tester, 'Rice 25kg', 5);
    await checkout(tester, 'Mami Siri Chop Bar');
    await openOutbox(tester);
    app.server.offline = false;
    app.server.stockAt(5, 'NKW', 2); // another rep sold 3 bags meanwhile
    await sendNow(tester);
    expect(find.textContaining('Needs attention'), findsOneWidget);
    expect(find.textContaining('Not enough Rice 25kg'), findsOneWidget, reason: "Show the server's reason.");
    await sendNow(tester);
    expect(app.server.requests.length, 1, reason: 'A refused order is not sent again unchanged.');
  });
}
