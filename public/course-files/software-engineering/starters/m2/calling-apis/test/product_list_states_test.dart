// Self-check for "Calling APIs from the App": the four states of the product list.
// Run it:  flutter test test/product_list_states_test.dart
// Do not change this file.
import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:material_ui/material_ui.dart';
import 'package:nkwen_rep_app/screens/product_list_screen.dart';
import 'package:nkwen_rep_app/widgets/product_card.dart';

import 'helpers.dart';

http.Response products(List<dynamic> list) => http.Response(
    jsonEncode({'count': list.length, 'currency': 'FCFA', 'products': list}), 200,
    headers: {'content-type': 'application/json'});

List<dynamic> all() => productsFixture()['products'] as List<dynamic>;

void main() {
  testWidgets('loading: a spinner while the API has not answered', (tester) async {
    final api = fakeApi(answer: (r) async {
      await Future.delayed(const Duration(seconds: 1));
      return products(all());
    });
    await tester.pumpWidget(appWith(const ProductListScreen(), api: api));
    await tester.pump(const Duration(milliseconds: 300));
    expect(find.byType(CircularProgressIndicator), findsOneWidget);
    await tester.pumpAndSettle();
    expect(find.byType(ProductCard), findsWidgets);
  });

  testWidgets('error: the ApiException message and a working "Try again"', (tester) async {
    var calls = 0;
    final api = fakeApi(answer: (r) async {
      calls++;
      if (calls == 1) throw http.ClientException('Failed host lookup');
      return products(all());
    });
    await tester.pumpWidget(appWith(const ProductListScreen(), api: api));
    await tester.pumpAndSettle();
    expect(find.textContaining('ClientException'), findsNothing, reason: 'Show ApiException.message, not the raw error.');
    expect(find.textContaining('Failed host lookup'), findsNothing);
    expect(find.text('Try again'), findsOneWidget);
    await tester.tap(find.text('Try again'));
    await tester.pumpAndSettle();
    expect(find.text('Rice 50kg'), findsOneWidget);
  });

  testWidgets('search: sent to the server as ?search=, and an empty answer says so', (tester) async {
    final asked = <Uri>[];
    final api = fakeApi(answer: (r) async {
      asked.add(r.url);
      final s = r.url.queryParameters['search'];
      if (s == null) return products(all());
      return products(all().where((p) => (p['name'] as String).toLowerCase().contains(s.toLowerCase())).toList());
    });
    await tester.pumpWidget(appWith(const ProductListScreen(), api: api));
    await tester.pumpAndSettle();
    expect(find.byKey(const ValueKey('product-search')), findsOneWidget,
        reason: "Add a search TextField with key ValueKey('product-search').");
    await tester.enterText(find.byKey(const ValueKey('product-search')), 'rice');
    await tester.testTextInput.receiveAction(TextInputAction.search);
    await tester.pumpAndSettle();
    expect(asked.last.queryParameters['search'], 'rice', reason: 'The server filters: send ?search=rice.');
    expect(find.byType(ProductCard), findsNWidgets(2));
    await tester.enterText(find.byKey(const ValueKey('product-search')), 'zzz');
    await tester.testTextInput.receiveAction(TextInputAction.search);
    await tester.pumpAndSettle();
    expect(find.byType(ProductCard), findsNothing);
    expect(find.textContaining('zzz'), findsWidgets, reason: 'The empty state should say what was searched.');
    expect(find.textContaining('No products'), findsOneWidget, reason: 'Empty state: say that nothing matches.');
  });

  testWidgets('pull down to refresh asks the server again', (tester) async {
    var calls = 0;
    final api = fakeApi(answer: (r) async {
      calls++;
      return products(all());
    });
    await tester.pumpWidget(appWith(const ProductListScreen(), api: api));
    await tester.pumpAndSettle();
    expect(find.byType(RefreshIndicator), findsOneWidget, reason: 'Wrap the list in a RefreshIndicator.');
    await tester.fling(find.byType(ProductCard).first, const Offset(0, 400), 1000);
    await tester.pumpAndSettle();
    expect(calls, 2);
  });
}
