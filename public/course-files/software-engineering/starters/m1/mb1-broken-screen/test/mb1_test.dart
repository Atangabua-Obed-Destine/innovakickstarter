// Self-check for "Problem Solving MB1: Fix the Broken Screen".
// Run it:  flutter test test/mb1_test.dart
// Do not change this file. Fix the code in lib/mb1/ until every test passes.
// Tip: a test that fails prints the real Flutter error. Read it from the top.
import 'package:flutter_test/flutter_test.dart';
import 'package:material_ui/material_ui.dart';
import 'package:nkwen_rep_app/mb1/main_mb1.dart';

Future<void> openQuickOrder(WidgetTester tester, {double width = 800}) async {
  tester.view.physicalSize = Size(width * 3, 900 * 3);
  tester.view.devicePixelRatio = 3;
  addTearDown(tester.view.reset);
  await tester.pumpWidget(const Mb1App());
}

void main() {
  testWidgets('1. the screen opens without an error', (tester) async {
    await openQuickOrder(tester);
    expect(find.text('Quick order'), findsOneWidget);
    expect(find.text('Rice 50kg · 36,005 FCFA'), findsOneWidget);
  });

  testWidgets('2. long product names fit on a 360 px phone', (tester) async {
    await openQuickOrder(tester, width: 360);
    expect(find.text('Vegetable Oil 5L · 7,210 FCFA'), findsOneWidget);
  });

  testWidgets('3. choosing Limbe shows 0 instead of crashing', (tester) async {
    await openQuickOrder(tester);
    await tester.tap(find.byType(DropdownButton<String>));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Limbe Down Beach').last);
    await tester.pumpAndSettle();
    expect(find.text('Stock at Limbe Down Beach: 0'), findsWidgets);
  });

  testWidgets('4. the More button changes the number on screen', (tester) async {
    await openQuickOrder(tester);
    final qty = find.byKey(const ValueKey('mb1-qty-16'));
    expect(tester.widget<Text>(qty).data, '1');
    await tester.tap(find.byTooltip('More').first);
    await tester.pump();
    expect(tester.widget<Text>(qty).data, '2');
  });

  testWidgets('5. the cart count in the top bar follows the cart', (tester) async {
    await openQuickOrder(tester);
    expect(find.text('Cart: 0'), findsOneWidget);
    await tester.tap(find.byTooltip('Add to cart').first);
    await tester.pump();
    expect(find.text('Cart: 1'), findsOneWidget);
  });
}
