// Self-check for "State in a Mobile App" (updated in the M1 project: the list now loads from a fake API).
// Run it:  flutter test test/state_test.dart
// Do not change this file. If a test fails, read its name and reason, then fix your code.
import 'package:flutter_test/flutter_test.dart';
import 'package:material_ui/material_ui.dart';
import 'package:nkwen_rep_app/data/customers.dart';
import 'package:nkwen_rep_app/data/sample_products.dart';
import 'package:nkwen_rep_app/models/product.dart';
import 'package:nkwen_rep_app/screens/order_draft_screen.dart';
import 'package:nkwen_rep_app/screens/product_list_screen.dart';
import 'package:nkwen_rep_app/state/order_draft.dart';
import 'package:nkwen_rep_app/widgets/basket_button.dart';
import 'package:nkwen_rep_app/widgets/product_card.dart';

import 'helpers.dart';

Product byName(String name) => sampleProducts.firstWhere((p) => p.name == name);

// Count price x quantity by hand, to compare with the draft.
int byHand(Map<String, int> lines) =>
    lines.entries.fold(0, (sum, e) => sum + byName(e.key).priceFcfa * e.value);

String badgeText(WidgetTester tester) {
  final texts = find.descendant(of: find.byType(BasketButton), matching: find.byType(Text));
  return texts.evaluate().isEmpty ? '' : (tester.widget<Text>(texts.first).data ?? '');
}

void main() {
  group('OrderDraft (no screens)', () {
    test('a new draft is empty, from Nkwen, paid in cash', () {
      final draft = OrderDraft();
      expect(draft.isEmpty, isTrue);
      expect(draft.itemCount, 0);
      expect(draft.totalFcfa, 0);
      expect(draft.branch, 'NKW');
      expect(draft.paymentMethod, 'cash');
      expect(draft.customer, isNull);
    });

    test('adding the same product twice makes one line', () {
      final draft = OrderDraft()
        ..add(byName('Rice 50kg'), 2)
        ..add(byName('Rice 50kg'), 1);
      expect(draft.lines.length, 1, reason: 'TODO 4: add to the existing line.');
      expect(draft.quantityOf(6), 3);
      expect(draft.itemCount, 3, reason: 'TODO 2: itemCount is the sum of the quantities.');
      expect(draft.totalFcfa, byHand({'Rice 50kg': 3}), reason: 'TODO 3: totalFcfa is the sum of the line totals.');
    });

    test('the total adds every line', () {
      final draft = OrderDraft()
        ..add(byName('Bread (loaf)'), 4)
        ..add(byName('Palm Oil 5L'), 2)
        ..add(byName('Rice 50kg'), 1);
      expect(draft.lines.length, 3);
      expect(draft.itemCount, 7);
      expect(draft.totalFcfa, byHand({'Bread (loaf)': 4, 'Palm Oil 5L': 2, 'Rice 50kg': 1}));
    });

    test('add ignores 0 and negative quantities, and caps at 100', () {
      final draft = OrderDraft()
        ..add(byName('Bread (loaf)'), 0)
        ..add(byName('Bread (loaf)'), -3);
      expect(draft.isEmpty, isTrue);
      draft
        ..add(byName('Bread (loaf)'), 90)
        ..add(byName('Bread (loaf)'), 20);
      expect(draft.quantityOf(1), OrderDraft.maxQuantity, reason: 'The Orders API refuses more than 100.');
    });

    test('setQuantity changes a line, removes it at 0 and caps at 100', () {
      final draft = OrderDraft()..add(byName('Rice 25kg'), 2);
      draft.setQuantity(5, 7);
      expect(draft.quantityOf(5), 7, reason: 'TODO 5.');
      draft.setQuantity(5, 150);
      expect(draft.quantityOf(5), 100);
      draft.setQuantity(999, 3); // unknown product: nothing happens
      expect(draft.lines.length, 1);
      draft.setQuantity(5, 0);
      expect(draft.isEmpty, isTrue, reason: 'Quantity 0 removes the line.');
    });

    test('remove deletes one line', () {
      final draft = OrderDraft()
        ..add(byName('Rice 25kg'), 2)
        ..add(byName('Bread (loaf)'), 1);
      draft.remove(5);
      expect(draft.lines.map((l) => l.product.id), [1], reason: 'TODO 6.');
    });

    test('every change notifies the listeners', () {
      final draft = OrderDraft();
      var calls = 0;
      draft.addListener(() => calls++);
      draft.add(byName('Rice 25kg'), 2);
      expect(calls, 1, reason: 'add() must call notifyListeners().');
      draft.setQuantity(5, 3);
      expect(calls, 2, reason: 'setQuantity() must call notifyListeners().');
      draft.remove(5);
      expect(calls, 3, reason: 'remove() must call notifyListeners().');
      draft.setBranch('ML4');
      draft.setPaymentMethod('mtn_momo');
      expect(calls, 5, reason: 'TODO 7: setBranch() and setPaymentMethod() must call notifyListeners().');
      expect(draft.branch, 'ML4');
      expect(draft.paymentMethod, 'mtn_momo');
    });

    test('screens cannot change the list of lines directly', () {
      final draft = OrderDraft()..add(byName('Rice 25kg'), 1);
      expect(() => draft.lines.add(OrderLine(product: byName('Bread (loaf)'), quantity: 1)), throwsUnsupportedError,
          reason: 'TODO 1: return List.unmodifiable(_lines).');
    });

    test('clear() starts a new order but keeps the branch and payment method', () {
      final draft = OrderDraft()
        ..setBranch('CAV')
        ..setPaymentMethod('orange_money')
        ..setCustomer(sampleCustomers.first)
        ..add(byName('Rice 25kg'), 1);
      draft.clear();
      expect(draft.isEmpty, isTrue, reason: 'TODO 8.');
      expect(draft.customer, isNull);
      expect(draft.branch, 'CAV');
      expect(draft.paymentMethod, 'orange_money');
    });
  });

  group('Screens that share the draft', () {
    testWidgets('adding on the detail screen updates the basket on the list', (tester) async {
      final draft = OrderDraft();
      await tester.pumpWidget(appWith(const ProductListScreen(), draft: draft));
      await tester.pumpAndSettle(); // wait for the (fake) API to answer
      expect(find.byType(BasketButton), findsOneWidget, reason: 'Put const BasketButton() in the AppBar actions of the list.');
      await tester.scrollUntilVisible(find.text('Rice 50kg'), 200, scrollable: find.ancestor(of: find.byType(ProductCard).first, matching: find.byType(Scrollable)).first);
      await tester.ensureVisible(find.text('Rice 50kg'));
      await tester.pumpAndSettle();
      await tester.tap(find.text('Rice 50kg'));
      await tester.pumpAndSettle();
      await tester.tap(find.byTooltip('More'));
      await tester.pump();
      await tester.tap(find.text('Add to order'));
      await tester.pumpAndSettle();
      expect(draft.quantityOf(6), 2, reason: 'The detail screen must call context.read<OrderDraft>().add(...).');
      expect(badgeText(tester), '2', reason: 'TODO 9 and 10: the badge watches the draft.');
    });

    testWidgets('the basket opens the order draft with lines and the total', (tester) async {
      final draft = OrderDraft()
        ..add(byName('Bread (loaf)'), 4)
        ..add(byName('Palm Oil 5L'), 2);
      await tester.pumpWidget(appWith(const ProductListScreen(), draft: draft));
      await tester.pumpAndSettle(); // wait for the (fake) API to answer
      await tester.tap(find.byType(BasketButton));
      await tester.pumpAndSettle();
      expect(find.byType(OrderDraftScreen), findsOneWidget, reason: 'TODO 11: open OrderDraftScreen.');
      expect(find.text('Palm Oil 5L'), findsOneWidget, reason: 'TODO 12 and 13: show every line.');
      expect(find.text('13,200 FCFA'), findsOneWidget, reason: 'Show the line total: 6,600 x 2.');
      expect(find.text('Estimated total: 15,600 FCFA'), findsOneWidget);
    });

    testWidgets('More, Less and Remove on the order draft change the total and the basket', (tester) async {
      final draft = OrderDraft()..add(byName('Rice 25kg'), 1);
      await tester.pumpWidget(appWith(const Scaffold(body: Center(child: BasketButton())), draft: draft));
      await tester.tap(find.byType(BasketButton));
      await tester.pumpAndSettle();
      await tester.tap(find.byTooltip('More'));
      await tester.pump();
      expect(draft.quantityOf(5), 2);
      expect(find.text('Estimated total: 38,110 FCFA'), findsOneWidget);
      await tester.tap(find.byTooltip('Less'));
      await tester.pump();
      expect(find.text('Estimated total: 19,055 FCFA'), findsOneWidget);
      await tester.tap(find.byTooltip('Remove'));
      await tester.pump();
      expect(find.text('No items yet. Add products from the list.'), findsOneWidget);
      await tester.pageBack();
      await tester.pumpAndSettle();
      expect(badgeText(tester), anyOf('', '0'), reason: 'The badge label is hidden when the draft is empty.');
    });

    testWidgets('the customer chosen on the list appears on the order draft', (tester) async {
      await tester.pumpWidget(appWith(const ProductListScreen()));
      await tester.pumpAndSettle(); // wait for the (fake) API to answer
      await tester.tap(find.byTooltip('Customers'));
      await tester.pumpAndSettle();
      await tester.tap(find.text('Kamga Alimentation'));
      await tester.pumpAndSettle();
      expect(find.text('Visiting: Kamga Alimentation'), findsOneWidget);
      await tester.tap(find.byType(BasketButton));
      await tester.pumpAndSettle();
      expect(find.text('Customer: Kamga Alimentation'), findsOneWidget,
          reason: 'The list must save the customer in the OrderDraft (setCustomer), not in its own State.');
    });

    testWidgets('the order draft fits a small phone with large text', (tester) async {
      usePhoneSize(tester, width: 320, height: 568);
      final draft = OrderDraft()
        ..add(byName('Maggi Cubes (pack)'), 12)
        ..add(byName('Vegetable Oil 5L'), 3);
      await tester.pumpWidget(appWith(const OrderDraftScreen(), draft: draft, textScale: 1.3));
      // An overflow anywhere on the way down makes this test fail.
      await tester.scrollUntilVisible(find.text('Vegetable Oil 5L'), 100, scrollable: find.byType(Scrollable).first);
      expect(find.text('Vegetable Oil 5L'), findsOneWidget);
    });
  });
}
