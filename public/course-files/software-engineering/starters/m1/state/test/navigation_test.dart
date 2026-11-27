// Self-check for "Navigation Between Screens" (updated in "State in a Mobile App":
// the screens now get an OrderDraft, as main.dart gives them).
// Run it:  flutter test test/navigation_test.dart
// Do not change this file. If a test fails, read its name and reason, then fix your code.
import 'package:flutter_test/flutter_test.dart';
import 'package:material_ui/material_ui.dart';
import 'package:nkwen_rep_app/data/customers.dart';
import 'package:nkwen_rep_app/data/sample_products.dart';
import 'package:nkwen_rep_app/models/customer.dart';
import 'package:nkwen_rep_app/screens/customer_list_screen.dart';
import 'package:nkwen_rep_app/screens/product_detail_screen.dart';
import 'package:nkwen_rep_app/screens/product_list_screen.dart';
import 'package:nkwen_rep_app/widgets/product_card.dart';
import 'package:nkwen_rep_app/state/order_draft.dart';

import 'helpers.dart';

Future<void> openApp(WidgetTester tester, {String branch = 'NKW'}) async {
  // Since "State in a Mobile App", the branch lives in the shared OrderDraft.
  await tester.pumpWidget(appWith(const ProductListScreen(products: sampleProducts), draft: OrderDraft()..setBranch(branch)));
}

// Tap a product card on the list and wait for the new screen to finish sliding in.
Future<void> openProduct(WidgetTester tester, String name) async {
  // Scroll the product list until the card is on screen, then tap it.
  await tester.scrollUntilVisible(find.text(name), 200, scrollable: find.ancestor(of: find.byType(ProductCard).first, matching: find.byType(Scrollable)).first);
  await tester.ensureVisible(find.text(name));
  await tester.pumpAndSettle();
  await tester.tap(find.text(name));
  await tester.pumpAndSettle();
}

int shownQuantity(WidgetTester tester) {
  final text = tester.widget<Text>(find.byKey(const ValueKey('quantity')));
  return int.parse(text.data!);
}

void main() {
  testWidgets('tapping a product opens its detail screen', (tester) async {
    await openApp(tester);
    await openProduct(tester, 'Rice 50kg');
    expect(find.byType(ProductDetailScreen), findsOneWidget, reason: 'TODO 1: push ProductDetailScreen.');
    expect(find.text('36,005 FCFA'), findsOneWidget);
    expect(find.text('Grains · bag · NKT-GRA-006'), findsOneWidget);
  });

  testWidgets('the detail screen shows stock at the four open branches', (tester) async {
    await openApp(tester);
    await openProduct(tester, 'Rice 50kg');
    for (final line in ['Nkwen: 42', 'Mile 4: 3', 'Commercial Avenue: 58', 'Bafoussam Centre: 21']) {
      expect(find.text(line), findsOneWidget, reason: 'TODO 6: show "$line".');
    }
    expect(find.textContaining('Limbe'), findsNothing, reason: 'Limbe is not open yet.');
  });

  testWidgets('Back returns to the list without adding anything', (tester) async {
    await openApp(tester);
    await openProduct(tester, 'Rice 50kg');
    await tester.pageBack();
    await tester.pumpAndSettle();
    expect(find.byType(ProductDetailScreen), findsNothing);
    expect(find.text('Products at Nkwen'), findsOneWidget);
    expect(find.byType(SnackBar), findsNothing, reason: 'Back gives null: nothing was added.');
  });

  testWidgets('More and Less change the quantity, never below 1', (tester) async {
    await openApp(tester);
    await openProduct(tester, 'Rice 50kg');
    expect(find.byKey(const ValueKey('quantity')), findsOneWidget, reason: "TODO 7: Text('\$_quantity', key: const ValueKey('quantity')).");
    expect(shownQuantity(tester), 1);
    await tester.tap(find.byTooltip('More'));
    await tester.tap(find.byTooltip('More'));
    await tester.pump();
    expect(shownQuantity(tester), 3, reason: 'Change _quantity inside setState() so the screen redraws.');
    for (var i = 0; i < 4; i++) {
      await tester.tap(find.byTooltip('Less'), warnIfMissed: false);
      await tester.pump();
    }
    expect(shownQuantity(tester), 1, reason: 'The quantity never goes below 1.');
  });

  testWidgets('More stops at the stock of the branch', (tester) async {
    await openApp(tester, branch: 'ML4');
    await openProduct(tester, 'Rice 50kg'); // 3 bags at Mile 4
    for (var i = 0; i < 5; i++) {
      await tester.tap(find.byTooltip('More'), warnIfMissed: false);
      await tester.pump();
    }
    expect(shownQuantity(tester), 3, reason: 'Mile 4 has only 3 bags: More must stop at _maxQuantity.');
  });

  testWidgets('"Add to order" returns the quantity to the list, which shows a SnackBar', (tester) async {
    await openApp(tester);
    await openProduct(tester, 'Rice 50kg');
    await tester.tap(find.byTooltip('More'));
    await tester.tap(find.byTooltip('More'));
    await tester.pump();
    await tester.tap(find.text('Add to order'));
    await tester.pumpAndSettle();
    expect(find.byType(ProductDetailScreen), findsNothing, reason: 'TODO 8: Navigator.pop(context, _quantity).');
    expect(find.text('Added 3 x Rice 50kg'), findsOneWidget, reason: 'TODO 2: show the SnackBar on the list screen.');
  });

  testWidgets('a product with no stock at the branch cannot be added', (tester) async {
    await openApp(tester, branch: 'ML4');
    await openProduct(tester, 'Bread (loaf)'); // 0 loaves at Mile 4
    expect(find.text('Out of stock at Mile 4'), findsOneWidget, reason: 'TODO 7: say it is out of stock at this branch.');
    if (find.text('Add to order').evaluate().isNotEmpty) {
      await tester.tap(find.text('Add to order'), warnIfMissed: false);
      await tester.pumpAndSettle();
    }
    expect(find.byType(ProductDetailScreen), findsOneWidget, reason: 'TODO 8: the button must be disabled (onPressed: null).');
  });

  testWidgets('choosing a customer shows "Visiting: ..." on the list', (tester) async {
    await openApp(tester);
    expect(find.byTooltip('Customers'), findsOneWidget, reason: 'TODO 4: add the Customers button.');
    await tester.tap(find.byTooltip('Customers'));
    await tester.pumpAndSettle();
    expect(find.text('Choose a customer'), findsOneWidget, reason: 'TODO 3: push CustomerListScreen.');
    await tester.tap(find.text('Kamga Alimentation'));
    await tester.pumpAndSettle();
    expect(find.text('Visiting: Kamga Alimentation'), findsOneWidget, reason: 'TODO 3 and 5: save the customer and show it.');
  });

  testWidgets('the customer list gives back the customer that was tapped', (tester) async {
    Customer? result;
    await tester.pumpWidget(MaterialApp(
      home: Builder(
        builder: (context) => TextButton(
          onPressed: () async {
            result = await Navigator.push<Customer>(
              context,
              MaterialPageRoute(builder: (_) => const CustomerListScreen(customers: sampleCustomers)),
            );
          },
          child: const Text('open'),
        ),
      ),
    ));
    await tester.tap(find.text('open'));
    await tester.pumpAndSettle();
    expect(find.text('Bafoussam · Wholesale'), findsOneWidget, reason: "TODO 9: subtitle is town · Wholesale (or Walk-in).");
    expect(find.text('Bafoussam · Walk-in'), findsOneWidget);
    await tester.tap(find.text('Mami Siri Chop Bar'));
    await tester.pumpAndSettle();
    expect(result?.id, 3, reason: 'TODO 9: Navigator.pop(context, customer).');
  });

  testWidgets('the detail screen fits a small phone with large text', (tester) async {
    tester.view.physicalSize = const Size(320 * 3, 568 * 3);
    tester.view.devicePixelRatio = 3;
    addTearDown(tester.view.reset);
    await tester.pumpWidget(MaterialApp(
      builder: (context, page) => MediaQuery(
        data: MediaQuery.of(context).copyWith(textScaler: const TextScaler.linear(1.3)),
        child: page!,
      ),
      home: ProductDetailScreen(product: sampleProducts.firstWhere((p) => p.id == 7), branch: 'NKW'),
    ));
    expect(find.text('Maggi Cubes (pack)'), findsOneWidget);
  });
}
