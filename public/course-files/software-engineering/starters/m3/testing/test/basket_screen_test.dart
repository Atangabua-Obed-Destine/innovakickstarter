// Widget tests: they build one screen in a pretend phone and tap it.
// Run them with:  flutter test test/basket_screen_test.dart
import 'package:material_ui/material_ui.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:nkwen_basket_tests/basket.dart';
import 'package:nkwen_basket_tests/basket_screen.dart';
import 'package:nkwen_basket_tests/main.dart' show nkwProducts;

/// Puts the basket screen inside a MaterialApp, as the real app does.
/// [orders] collects every basket the rep sends.
Future<void> showBasketScreen(WidgetTester tester, List<Basket> orders) {
  return tester.pumpWidget(MaterialApp(
    home: BasketScreen(products: nkwProducts, onPlaceOrder: orders.add),
  ));
}

void main() {
  // A finished example.
  testWidgets('shows every product with its price', (tester) async {
    await showBasketScreen(tester, []);

    expect(find.text('Rice 50kg'), findsOneWidget);
    expect(find.textContaining('36,005 FCFA'), findsOneWidget);
    expect(find.byType(ListTile), findsNWidgets(nkwProducts.length));
  });

  testWidgets('"Place order" is switched off while the basket is empty', (tester) async {
    // TODO: show the screen, find the button with
    //   tester.widget<FilledButton>(find.widgetWithText(FilledButton, 'Place order'))
    // and check that its onPressed is null.
    fail('TODO: write the empty basket test');
  });

  testWidgets('tapping + on Rice 50kg twice updates the total', (tester) async {
    // TODO: tap find.byKey(const Key('add-6')) twice. After each tap, call
    // await tester.pump() so the screen rebuilds.
    // Check the summary text shows 2 items and the right total.
    fail('TODO: write the tap test');
  });

  testWidgets('the rep sees a clear message when stock runs out', (tester) async {
    // TODO: Rice 25kg (id 5) has 5 bags. Tap its + button 6 times.
    // Check: a SnackBar message starting with 'Not enough stock' appears,
    // and the summary still says 5 items.
    fail('TODO: write the stock message test');
  });

  testWidgets('"Place order" sends the basket', (tester) async {
    // TODO: add one Soap (bar) (id 13), tap 'Place order',
    // then check that orders has 1 basket with a total of 410.
    fail('TODO: write the place order test');
  });
}
