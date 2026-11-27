import 'package:material_ui/material_ui.dart';

import 'payment_flow.dart';

/// The rep's payment screen for one order.
class PayScreen extends StatefulWidget {
  const PayScreen({super.key, required this.flow});

  final PaymentFlow flow;

  @override
  State<PayScreen> createState() => _PayScreenState();
}

class _PayScreenState extends State<PayScreen> {
  final phone = TextEditingController();

  @override
  void dispose() {
    phone.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final flow = widget.flow;
    return Scaffold(
      appBar: AppBar(title: Text('Pay order ${flow.orderId}')),
      body: ListenableBuilder(
        listenable: flow,
        builder: (context, _) => ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text('Total: ${fcfa(flow.totalFcfa)}', style: Theme.of(context).textTheme.headlineSmall),
            if (flow.sandbox)
              const Text('SANDBOX: no real money moves. Use a test number.', style: TextStyle(color: Colors.deepOrange)),
            const SizedBox(height: 16),
            TextField(
              key: const Key('phone'),
              controller: phone,
              enabled: !flow.busy && flow.step != PayStep.paid,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(labelText: "Customer's MTN MoMo number", border: OutlineInputBorder()),
            ),
            const SizedBox(height: 16),
            if (flow.busy) const LinearProgressIndicator(),
            if (flow.message.isNotEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 12),
                child: Text(flow.message, key: const Key('message')),
              ),
            if (flow.step == PayStep.paid)
              const Chip(avatar: Icon(Icons.check_circle, color: Colors.green), label: Text('PAID'))
            else if (flow.step == PayStep.unknown && flow.reference != null)
              OutlinedButton(onPressed: flow.checkAgain, child: const Text('Check again'))
            else
              FilledButton(
                key: const Key('pay'),
                onPressed: flow.busy ? null : () => flow.pay(phone.text),
                child: Text(flow.step == PayStep.failed ? 'Try again' : 'Ask customer to pay'),
              ),
          ],
        ),
      ),
    );
  }
}
