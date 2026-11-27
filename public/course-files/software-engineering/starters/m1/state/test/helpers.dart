// Shared helpers for the self-check tests. Do not change this file.
import 'package:flutter_test/flutter_test.dart';
import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import 'package:nkwen_rep_app/state/order_draft.dart';

// Show [home] inside a MaterialApp, with an OrderDraft above it (like main.dart does).
Widget appWith(Widget home, {OrderDraft? draft, double textScale = 1.0}) {
  return ChangeNotifierProvider<OrderDraft>.value(
    value: draft ?? OrderDraft(),
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
