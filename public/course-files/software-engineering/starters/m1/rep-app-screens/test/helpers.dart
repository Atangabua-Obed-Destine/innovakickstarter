// Shared helpers for the self-check tests. Do not change this file.
// Updated in "Project: Sales-Rep App Screens": the screens now also get an NkwenApi.
import 'dart:convert';
import 'dart:io';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import 'package:nkwen_rep_app/services/nkwen_api.dart';
import 'package:nkwen_rep_app/state/order_draft.dart';

// The real answer of GET /products, saved on 25 Nov 2026 (20 products).
Map<String, dynamic> productsFixture() =>
    jsonDecode(File('test/fixtures/products.json').readAsStringSync()) as Map<String, dynamic>;

// A fake API: no internet needed. [answer] decides what each request gets back.
NkwenApi fakeApi({Future<http.Response> Function(http.Request request)? answer}) {
  return NkwenApi(
    baseUrl: 'https://api.test/nkwen',
    client: MockClient(answer ??
        (request) async => http.Response(jsonEncode(productsFixture()), 200,
            headers: {'content-type': 'application/json; charset=utf-8'})),
  );
}

// Show [home] inside a MaterialApp, with an NkwenApi and an OrderDraft above it (like app.dart).
Widget appWith(Widget home, {OrderDraft? draft, NkwenApi? api, double textScale = 1.0}) {
  return MultiProvider(
    providers: [
      Provider<NkwenApi>.value(value: api ?? fakeApi()),
      ChangeNotifierProvider<OrderDraft>.value(value: draft ?? OrderDraft()),
    ],
    child: MaterialApp(
      builder: (context, page) => MediaQuery(
        data: MediaQuery.of(context).copyWith(textScaler: TextScaler.linear(textScale)),
        child: page!,
      ),
      home: home,
    ),
  );
}

// Pretend the screen is a small phone: [width] x [height] logical pixels.
void usePhoneSize(WidgetTester tester, {double width = 360, double height = 640}) {
  tester.view.physicalSize = Size(width * 3, height * 3);
  tester.view.devicePixelRatio = 3;
  addTearDown(tester.view.reset);
}
