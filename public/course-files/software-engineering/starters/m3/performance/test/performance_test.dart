// Self-checks: is the history screen kind to a cheap phone?
// Do not change them: change lib/order_history.dart. Run:  flutter test
import 'package:material_ui/material_ui.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:nkwen_performance/order_history.dart';

final orders = List.generate(
  2000,
  (i) => PastOrder('ORD-${10000 + i}', 'Customer $i', 410 * (1 + i % 50), DateTime(2026, 1, 1).add(Duration(hours: 3 * i))),
);

Future<void> showHistory(WidgetTester tester) async {
  WorkCounter.reset();
  await tester.pumpWidget(MaterialApp(
    home: OrderHistory(orders: orders, photoUrl: 'https://example.com/shop.jpg'),
  ));
}

void main() {
  testWidgets('opening the screen does work only for the rows you can see', (tester) async {
    await showHistory(tester);
    expect(find.byType(ListTile), findsWidgets);
    expect(WorkCounter.describes, lessThan(100),
        reason: 'describe() ran ${WorkCounter.describes} times for one screen of rows. Build rows lazily.');
  });

  testWidgets('the newest order is at the top', (tester) async {
    await showHistory(tester);
    expect(find.text('Customer 1999'), findsOneWidget);
  });

  testWidgets('flipping the switch does not sort 2,000 orders again', (tester) async {
    await showHistory(tester);
    for (var i = 0; i < 3; i++) {
      await tester.tap(find.byType(Switch));
      await tester.pump();
    }
    expect(WorkCounter.sorts, 1, reason: 'Sorted ${WorkCounter.sorts} times: sort when the data changes, not in build().');
  });

  testWidgets('the photo is decoded at the size it is shown', (tester) async {
    await showHistory(tester);
    final image = tester.widget<Image>(find.byType(Image).first);
    expect(image.image, isA<ResizeImage>(), reason: 'Give Image.network a cacheWidth.');
  });

  testWidgets('scrolling far down still works', (tester) async {
    await showHistory(tester);
    await tester.scrollUntilVisible(find.text('Customer 1500'), 2000, scrollable: find.byType(Scrollable).first);
    expect(find.text('Customer 1500'), findsOneWidget);
  });
}
