// An integration test runs the WHOLE app on a real phone or an emulator.
// Run it with a phone connected:  flutter test integration_test/app_test.dart
import 'package:material_ui/material_ui.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:nkwen_basket_tests/main.dart' as app;

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('a rep builds a basket and places an order', (tester) async {
    app.main();
    await tester.pumpAndSettle();

    // TODO: like a real rep would:
    // 1. tap + on Rice 50kg twice and on Palm Oil 5L once (keys 'add-6' and 'add-15'),
    // 2. tap 'Place order',
    // 3. check that the message 'Order ready to send: ...' shows the right total.
    // Use await tester.pumpAndSettle() after each tap.
    expect(find.byType(MaterialApp), findsOneWidget);
    fail('TODO: write the integration test');
  });
}
