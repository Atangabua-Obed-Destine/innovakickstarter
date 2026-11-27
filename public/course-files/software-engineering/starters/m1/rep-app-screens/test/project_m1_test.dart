// Self-check for "Project: Sales-Rep App Screens".
// Run every test:  flutter test
// These tests use a FAKE API with the real Nkwen data, so they work offline.
// Run the app on your phone (flutter run) to see the real API.
// Do not change this file.
import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:material_ui/material_ui.dart';
import 'package:nkwen_rep_app/data/customers.dart';
import 'package:nkwen_rep_app/models/product.dart';
import 'package:nkwen_rep_app/screens/customer_list_screen.dart';
import 'package:nkwen_rep_app/screens/order_draft_screen.dart';
import 'package:nkwen_rep_app/screens/product_list_screen.dart';
import 'package:nkwen_rep_app/state/order_draft.dart';
import 'package:nkwen_rep_app/widgets/basket_button.dart';
import 'package:nkwen_rep_app/widgets/product_card.dart';

import 'helpers.dart';

http.Response jsonResponse(Object body, [int status = 200]) =>
    http.Response(jsonEncode(body), status, headers: {'content-type': 'application/json; charset=utf-8'});

Product fixtureProduct(int id) =>
    Product.fromJson((productsFixture()['products'] as List).firstWhere((p) => p['id'] == id) as Map<String, dynamic>);

Future<void> chooseFromDropdown(WidgetTester tester, Key key, String label) async {
  await tester.tap(find.byKey(key));
  await tester.pumpAndSettle();
  await tester.tap(find.text(label).last);
  await tester.pumpAndSettle();
}

void main() {
  group('Product list from the API', () {
    testWidgets('shows a spinner while loading, then the products the API sent', (tester) async {
      final data = productsFixture();
      // Change one price, to prove the list really comes from the API and not from sample data.
      (data['products'] as List).firstWhere((p) => p['id'] == 6)['price_fcfa'] = 37500;
      final api = fakeApi(answer: (request) async {
        await Future.delayed(const Duration(milliseconds: 500));
        return jsonResponse(data);
      });
      await tester.pumpWidget(appWith(const ProductListScreen(), api: api));
      expect(find.byType(CircularProgressIndicator), findsOneWidget, reason: 'Show a spinner while the Future is not done.');
      await tester.pumpAndSettle();
      expect(find.byType(CircularProgressIndicator), findsNothing);
      expect(find.text('37,500 FCFA'), findsOneWidget, reason: 'Load the products with NkwenApi.fetchProducts(), not sampleProducts.');
    });

    testWidgets('asks the API only once, even when the screen redraws', (tester) async {
      var calls = 0;
      final api = fakeApi(answer: (request) async {
        calls++;
        return jsonResponse(productsFixture());
      });
      final draft = OrderDraft();
      await tester.pumpWidget(appWith(const ProductListScreen(), api: api, draft: draft));
      await tester.pumpAndSettle();
      draft.add(fixtureProduct(1), 1); // the badge changes, so the screen redraws
      await tester.pumpAndSettle();
      expect(calls, 1, reason: 'Start fetchProducts() in initState, not in build().');
    });

    testWidgets('a server error shows a message, and "Try again" loads again', (tester) async {
      var calls = 0;
      final api = fakeApi(answer: (request) async {
        calls++;
        if (calls == 1) return jsonResponse({'message': 'Service unavailable'}, 503);
        return jsonResponse(productsFixture());
      });
      await tester.pumpWidget(appWith(const ProductListScreen(), api: api));
      await tester.pumpAndSettle();
      expect(find.text('Try again'), findsOneWidget, reason: 'On an error, show a message and a "Try again" button.');
      expect(find.byType(ProductCard), findsNothing);
      await tester.tap(find.text('Try again'));
      await tester.pumpAndSettle();
      expect(calls, 2);
      expect(find.text('Rice 50kg'), findsOneWidget);
    });

    testWidgets('changing the branch changes the title and the stock badges', (tester) async {
      final draft = OrderDraft();
      await tester.pumpWidget(appWith(const ProductListScreen(), draft: draft));
      await tester.pumpAndSettle();
      expect(find.byKey(const ValueKey('branch-dropdown')), findsOneWidget, reason: 'TODO A: put a BranchDropdown above the list.');
      await chooseFromDropdown(tester, const ValueKey('branch-dropdown'), 'Mile 4');
      expect(draft.branch, 'ML4');
      expect(find.text('Products at Mile 4'), findsOneWidget);
      await tester.scrollUntilVisible(find.text('Rice 50kg'), 200, scrollable: find.ancestor(of: find.byType(ProductCard).first, matching: find.byType(Scrollable)).first);
      await tester.ensureVisible(find.text('Rice 50kg'));
      await tester.pumpAndSettle();
      final rice = find.ancestor(of: find.text('Rice 50kg'), matching: find.byType(ProductCard));
      expect(find.descendant(of: rice, matching: find.text('Only 3 left')), findsOneWidget);
    });
  });

  group('Customers', () {
    testWidgets('the search box filters by name or town', (tester) async {
      await tester.pumpWidget(appWith(const CustomerListScreen(customers: sampleCustomers)));
      expect(find.byType(TextField), findsOneWidget, reason: 'Add a search TextField.');
      await tester.enterText(find.byType(TextField), 'KAM');
      await tester.pump();
      expect(find.text('Kamga Alimentation'), findsOneWidget);
      expect(find.text('Tamfu & Sons Store'), findsNothing);
      await tester.enterText(find.byType(TextField), 'bafoussam');
      await tester.pump();
      expect(find.byType(ListTile), findsNWidgets(2), reason: 'Two customers are in Bafoussam.');
      await tester.enterText(find.byType(TextField), 'zzz');
      await tester.pump();
      expect(find.textContaining('No customer'), findsOneWidget, reason: 'Show a message when nothing matches.');
    });
  });

  group('Order draft', () {
    testWidgets('tapping the customer row opens the list and saves the choice', (tester) async {
      final draft = OrderDraft()..add(fixtureProduct(6), 1);
      await tester.pumpWidget(appWith(const OrderDraftScreen(), draft: draft));
      await tester.tap(find.text('No customer chosen'));
      await tester.pumpAndSettle();
      await tester.tap(find.text('Tamfu & Sons Store'));
      await tester.pumpAndSettle();
      expect(find.text('Customer: Tamfu & Sons Store'), findsOneWidget);
      expect(draft.customer?.id, 2);
    });

    testWidgets('the payment method can be chosen', (tester) async {
      final draft = OrderDraft()..add(fixtureProduct(6), 1);
      await tester.pumpWidget(appWith(const OrderDraftScreen(), draft: draft));
      expect(find.byKey(const ValueKey('payment-dropdown')), findsOneWidget, reason: "Add a DropdownButton with key ValueKey('payment-dropdown').");
      await chooseFromDropdown(tester, const ValueKey('payment-dropdown'), 'MTN Mobile Money');
      expect(draft.paymentMethod, 'mtn_momo', reason: 'Save the API code (mtn_momo), show the label (MTN Mobile Money).');
    });

    testWidgets('a line warns when the branch does not have enough', (tester) async {
      final draft = OrderDraft()..add(fixtureProduct(5), 5); // Rice 25kg: 5 at Nkwen, 3 at Commercial Avenue
      await tester.pumpWidget(appWith(const OrderDraftScreen(), draft: draft));
      expect(find.textContaining('Only'), findsNothing, reason: 'Nkwen has 5 bags: no warning.');
      await chooseFromDropdown(tester, const ValueKey('branch-dropdown'), 'Commercial Avenue');
      expect(find.text('Only 3 at Commercial Avenue'), findsOneWidget);
    });
  });

  testWidgets('the whole flow works on a 360 px phone', (tester) async {
    usePhoneSize(tester);
    final draft = OrderDraft();
    await tester.pumpWidget(appWith(const ProductListScreen(), draft: draft));
    await tester.pumpAndSettle();
    await tester.scrollUntilVisible(find.text('Palm Oil 5L'), 200, scrollable: find.ancestor(of: find.byType(ProductCard).first, matching: find.byType(Scrollable)).first);
    await tester.ensureVisible(find.text('Palm Oil 5L'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Palm Oil 5L'));
    await tester.pumpAndSettle();
    await tester.tap(find.byTooltip('More'));
    await tester.pump();
    await tester.tap(find.text('Add to order'));
    await tester.pumpAndSettle();
    await tester.tap(find.byType(BasketButton));
    await tester.pumpAndSettle();
    expect(find.text('Estimated total: 13,200 FCFA'), findsOneWidget);
  });
}
