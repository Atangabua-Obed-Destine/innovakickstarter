// A widget test for the payment screen. Run:  flutter test test/pay_screen_test.dart
import 'package:material_ui/material_ui.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:nkwen_payments/payments/pay_screen.dart';
import 'package:nkwen_payments/payments/payment_flow.dart';
import 'package:nkwen_payments/payments/sandbox_payment_api.dart';

Future<PaymentFlow> showPayScreen(WidgetTester tester, SandboxPaymentApi api) async {
  final flow = PaymentFlow(api: api, orderId: 'ORD-7Q2K9X1B', totalFcfa: 78610, wait: (_) async {});
  await tester.pumpWidget(MaterialApp(home: PayScreen(flow: flow)));
  return flow;
}

void main() {
  testWidgets('the rep enters a test number, taps pay, and sees PAID only at the end', (tester) async {
    final api = SandboxPaymentApi();
    await showPayScreen(tester, api);

    expect(find.text('Total: 78,610 FCFA'), findsOneWidget);
    expect(find.text('PAID'), findsNothing);

    await tester.enterText(find.byKey(const Key('phone')), '46733123456');
    await tester.tap(find.byKey(const Key('pay')));
    await tester.pumpAndSettle();

    expect(find.text('PAID'), findsOneWidget);
    expect(api.orderStatus['ORD-7Q2K9X1B'], 'paid');
  });

  testWidgets('a declined payment offers "Try again"', (tester) async {
    await showPayScreen(tester, SandboxPaymentApi());

    await tester.enterText(find.byKey(const Key('phone')), '46733123451');
    await tester.tap(find.byKey(const Key('pay')));
    await tester.pumpAndSettle();

    expect(find.text('Try again'), findsOneWidget);
    expect(find.text('PAID'), findsNothing);
  });
}
