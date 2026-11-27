// Self-check for "Widgets & Layout".
// Run it:  flutter test test/layout_test.dart
// Do not change this file. If a test fails, read its name and reason, then fix your code.
import 'package:flutter_test/flutter_test.dart';
import 'package:material_ui/material_ui.dart';
import 'package:nkwen_rep_app/data/sample_products.dart';
import 'package:nkwen_rep_app/format.dart';
import 'package:nkwen_rep_app/models/product.dart';
import 'package:nkwen_rep_app/widgets/product_card.dart';
import 'package:nkwen_rep_app/widgets/product_list_view.dart';
import 'package:nkwen_rep_app/widgets/stock_badge.dart';

// Pretend the screen is a small phone: [width] x [height] logical pixels.
void usePhoneSize(WidgetTester tester, {double width = 360, double height = 640}) {
  tester.view.physicalSize = Size(width * 3, height * 3);
  tester.view.devicePixelRatio = 3;
  addTearDown(tester.view.reset);
}

// Put a widget inside a MaterialApp, optionally with bigger text (as some reps set on their phones).
Widget inApp(Widget child, {double textScale = 1.0}) {
  return MaterialApp(
    builder: (context, page) => MediaQuery(
      data: MediaQuery.of(context).copyWith(textScaler: TextScaler.linear(textScale)),
      child: page!,
    ),
    home: Scaffold(body: child),
  );
}

Product byName(String name) => sampleProducts.firstWhere((p) => p.name == name);

void main() {
  group('format.dart', () {
    test('formatFcfa puts commas between thousands', () {
      expect(formatFcfa(600), '600 FCFA');
      expect(formatFcfa(36005), '36,005 FCFA');
      expect(formatFcfa(1000000), '1,000,000 FCFA');
      expect(formatFcfa(0), '0 FCFA');
    });

    test('stockText follows the Chapter 4 badge rule', () {
      expect(stockText(0), 'Out of stock');
      expect(stockText(3), 'Only 3 left');
      expect(stockText(5), 'Only 5 left');
      expect(stockText(6), 'In stock');
    });
  });

  group('Product', () {
    test('fromJson reads the API names (snake_case) and stockAt gives 0 for Limbe', () {
      final product = Product.fromJson({
        'id': 6, 'sku': 'NKT-GRA-006', 'name': 'Rice 50kg', 'category': 'Grains', 'unit': 'bag',
        'price_fcfa': 36005, 'stock': {'NKW': 42, 'ML4': 3}, 'reorder_level': 5,
      });
      expect(product.priceFcfa, 36005);
      expect(product.reorderLevel, 5);
      expect(product.stockAt('ML4'), 3);
      expect(product.stockAt('LMB'), 0, reason: 'LMB (Limbe) is not open yet, so it is not in the stock map.');
    });
  });

  group('StockBadge', () {
    testWidgets('shows the badge words', (tester) async {
      await tester.pumpWidget(inApp(const Column(children: [
        StockBadge(quantity: 0),
        StockBadge(quantity: 3),
        StockBadge(quantity: 42),
      ])));
      expect(find.text('Out of stock'), findsOneWidget);
      expect(find.text('Only 3 left'), findsOneWidget);
      expect(find.text('In stock'), findsOneWidget);
    });

    testWidgets('has a coloured, rounded background', (tester) async {
      await tester.pumpWidget(inApp(const StockBadge(quantity: 3)));
      final boxes = tester.widgetList<Container>(find.descendant(of: find.byType(StockBadge), matching: find.byType(Container)));
      final decorated = boxes.where((c) => c.decoration is BoxDecoration).toList();
      expect(decorated, isNotEmpty, reason: 'TODO 6: use a Container with a BoxDecoration.');
      final box = decorated.first.decoration as BoxDecoration;
      expect(box.color, isNotNull, reason: 'Give the BoxDecoration a colour.');
      expect(box.borderRadius, isNotNull, reason: 'Give the BoxDecoration rounded corners.');
    });
  });

  group('ProductCard', () {
    testWidgets('shows name, category and unit, price and stock for the branch', (tester) async {
      await tester.pumpWidget(inApp(ProductCard(product: byName('Rice 50kg'), branch: 'NKW')));
      expect(find.text('Rice 50kg'), findsOneWidget);
      expect(find.text('Grains · bag'), findsOneWidget, reason: "Show '\${product.category} · \${product.unit}'.");
      expect(find.text('36,005 FCFA'), findsOneWidget, reason: 'Show the price with formatFcfa().');
      expect(find.byType(StockBadge), findsOneWidget, reason: 'Use a StockBadge for the stock.');
      expect(find.text('In stock'), findsOneWidget);
      expect(find.byType(CircleAvatar), findsOneWidget);
    });

    testWidgets('shows the stock of the branch it is given', (tester) async {
      await tester.pumpWidget(inApp(ProductCard(product: byName('Rice 50kg'), branch: 'ML4')));
      expect(find.text('Only 3 left'), findsOneWidget, reason: 'Rice 50kg has 3 bags at Mile 4 (ML4).');
    });

    testWidgets('calls onTap when tapped', (tester) async {
      var taps = 0;
      await tester.pumpWidget(inApp(ProductCard(product: byName('Bread (loaf)'), branch: 'NKW', onTap: () => taps++)));
      await tester.tap(find.text('Bread (loaf)'));
      expect(taps, 1, reason: 'Wrap the card content in an InkWell with onTap: onTap.');
    });
  });

  group('ProductListView', () {
    testWidgets('scrolls through every product and uses a ListView', (tester) async {
      usePhoneSize(tester);
      await tester.pumpWidget(inApp(const ProductListView(products: sampleProducts, branch: 'NKW')));
      expect(find.byType(ListView), findsOneWidget, reason: 'TODO 9: use ListView.builder so the list can scroll.');
      await tester.scrollUntilVisible(find.text(sampleProducts.last.name), 200);
      expect(find.text(sampleProducts.last.name), findsOneWidget);
    });

    testWidgets('tells onTap which product was tapped', (tester) async {
      Product? tapped;
      await tester.pumpWidget(inApp(ProductListView(products: sampleProducts, branch: 'NKW', onTap: (p) => tapped = p)));
      await tester.tap(find.text('Rice 25kg'));
      expect(tapped?.id, 5, reason: 'Call onTap with the product of the card that was tapped.');
    });

    testWidgets('shows a message when there are no products', (tester) async {
      await tester.pumpWidget(inApp(const ProductListView(products: [], branch: 'NKW')));
      expect(find.text('No products to show.'), findsOneWidget, reason: 'TODO 8: an empty list needs a message.');
    });

    testWidgets('fits a small phone (320 px wide) with large text', (tester) async {
      // A RenderFlex overflow makes this test fail with "A RenderFlex overflowed by ... pixels".
      usePhoneSize(tester, width: 320, height: 568);
      await tester.pumpWidget(inApp(const ProductListView(products: sampleProducts, branch: 'ML4'), textScale: 1.3));
      expect(find.byType(ProductCard), findsWidgets);
      expect(find.text('Out of stock'), findsWidgets, reason: 'Bread (loaf) has 0 at Mile 4.');
    });
  });
}
