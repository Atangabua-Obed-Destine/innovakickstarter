// Self-check for "Forms & Validation on Mobile": the checkout form.
// Run it:  flutter test test/checkout_screen_test.dart
// Do not change this file.
import 'package:flutter_test/flutter_test.dart';
import 'package:material_ui/material_ui.dart';
import 'package:nkwen_rep_app/data/customers.dart';
import 'package:nkwen_rep_app/models/product.dart';
import 'package:nkwen_rep_app/screens/checkout_screen.dart';
import 'package:nkwen_rep_app/state/order_draft.dart';

import 'helpers.dart';

Product rice() => Product.fromJson((productsFixture()['products'] as List).firstWhere((p) => p['id'] == 6) as Map<String, dynamic>);

// Opens the checkout from a button, and keeps every value it pops with.
Future<List<CheckoutDetails?>> openCheckout(WidgetTester tester, OrderDraft draft, {Map<String, String> serverErrors = const {}}) async {
  final results = <CheckoutDetails?>[];
  await tester.pumpWidget(appWith(
    Builder(
      builder: (context) => Scaffold(
        body: Center(
          child: TextButton(
            onPressed: () async {
              results.add(await Navigator.push<CheckoutDetails>(
                context,
                MaterialPageRoute(builder: (_) => CheckoutScreen(serverErrors: serverErrors)),
              ));
            },
            child: const Text('open checkout'),
          ),
        ),
      ),
    ),
    draft: draft,
  ));
  await tester.tap(find.text('open checkout'));
  await tester.pumpAndSettle();
  return results;
}

Future<void> choosePayment(WidgetTester tester, String label) async {
  await tester.tap(find.byKey(const ValueKey('checkout-payment')));
  await tester.pumpAndSettle();
  await tester.tap(find.text(label).last);
  await tester.pumpAndSettle();
}

Future<void> save(WidgetTester tester) async {
  await tester.ensureVisible(find.byKey(const ValueKey('save-order')));
  await tester.tap(find.byKey(const ValueKey('save-order')));
  await tester.pumpAndSettle();
}

void main() {
  testWidgets('the form starts with the chosen customer', (tester) async {
    final draft = OrderDraft()
      ..add(rice(), 2)
      ..setCustomer(sampleCustomers[3]); // Kamga Alimentation
    await openCheckout(tester, draft);
    expect(find.widgetWithText(TextFormField, 'Kamga Alimentation'), findsOneWidget);
    expect(find.widgetWithText(TextFormField, '600000104'), findsOneWidget);
  });

  testWidgets('an empty name is refused and the screen stays open', (tester) async {
    final results = await openCheckout(tester, OrderDraft()..add(rice(), 1));
    await save(tester);
    expect(results, isEmpty, reason: 'TODO 7: do not pop when a field is not valid.');
    expect(find.text('Enter the customer name.'), findsOneWidget, reason: 'TODO 10: give the name field its validator.');
  });

  testWidgets('MTN Mobile Money needs a phone number; then the details come back cleaned', (tester) async {
    final results = await openCheckout(tester, OrderDraft()..add(rice(), 1));
    await tester.enterText(find.byKey(const ValueKey('customer-name')), '  Fon Pauline ');
    await choosePayment(tester, 'MTN Mobile Money');
    await save(tester);
    expect(results, isEmpty, reason: 'TODO 11: the phone validator must refuse an empty number for mobile money.');
    await tester.enterText(find.byKey(const ValueKey('customer-phone')), '+237 600 000 107');
    await save(tester);
    expect(results.length, 1);
    final details = results.single!;
    expect(details.customerName, 'Fon Pauline', reason: 'Trim the name.');
    expect(details.customerPhone, '600000107', reason: 'TODO 9: send the normalised phone.');
    expect(details.paymentMethod, 'mtn_momo');
    expect(details.branch, 'NKW');
  });

  testWidgets('cash needs no phone', (tester) async {
    final results = await openCheckout(tester, OrderDraft()..add(rice(), 1));
    await tester.enterText(find.byKey(const ValueKey('customer-name')), 'Ngwa Mini-Market');
    await save(tester);
    expect(results.single?.customerPhone, isNull);
    expect(results.single?.paymentMethod, 'cash');
  });

  testWidgets('the phone field accepts only digits, spaces and +', (tester) async {
    await openCheckout(tester, OrderDraft()..add(rice(), 1));
    await tester.enterText(find.byKey(const ValueKey('customer-phone')), '6O0-abc 000');
    await tester.pump();
    final field = tester.widget<EditableText>(find.descendant(of: find.byKey(const ValueKey('customer-phone')), matching: find.byType(EditableText)));
    expect(field.controller.text, '60 000', reason: 'TODO 11: add the FilteringTextInputFormatter.');
  });

  testWidgets('messages from a 422 answer are shown, and go away when the field is edited', (tester) async {
    await openCheckout(tester, OrderDraft()..add(rice(), 1), serverErrors: {
      'customer_name': 'The customer name field must be at least 2 characters.',
      'items.0.quantity': 'The items.0.quantity field must be at least 1.',
    });
    expect(find.text('The customer name field must be at least 2 characters.'), findsOneWidget);
    expect(find.text('The items.0.quantity field must be at least 1.'), findsOneWidget);
    await tester.enterText(find.byKey(const ValueKey('customer-name')), 'Mami Siri Chop Bar');
    await tester.pump();
    expect(find.text('The customer name field must be at least 2 characters.'), findsNothing);
  });

  testWidgets('the form fits a small phone with large text', (tester) async {
    usePhoneSize(tester, width: 320, height: 568);
    await tester.pumpWidget(appWith(const CheckoutScreen(), draft: OrderDraft()..add(rice(), 1), textScale: 1.3));
    await tester.pumpAndSettle();
    expect(find.text('Checkout'), findsOneWidget);
  });
}
