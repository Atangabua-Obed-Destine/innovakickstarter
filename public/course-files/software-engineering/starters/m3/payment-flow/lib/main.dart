import 'package:material_ui/material_ui.dart';

import 'payments/pay_screen.dart';
import 'payments/payment_flow.dart';
import 'payments/sandbox_payment_api.dart';

/// A demo: order ORD-7Q2K9X1B (2 x Rice 50kg + 1 x Palm Oil 5L at Commercial Avenue).
/// The sandbox answers after a short delay, like a real server.
void main() {
  final api = SandboxPaymentApi(latency: const Duration(milliseconds: 800));
  final flow = PaymentFlow(api: api, orderId: 'ORD-7Q2K9X1B', totalFcfa: 78610);
  runApp(MaterialApp(
    title: 'Nkwen Rep: payments',
    theme: ThemeData(colorSchemeSeed: Colors.green),
    home: PayScreen(flow: flow),
  ));
}
