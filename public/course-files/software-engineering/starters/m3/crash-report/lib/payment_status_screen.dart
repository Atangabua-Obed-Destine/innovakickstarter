import 'package:material_ui/material_ui.dart';

/// Shows "Checking payment..." and then the answer from the server.
class PaymentStatusScreen extends StatefulWidget {
  const PaymentStatusScreen({super.key, required this.checkPayment});

  /// Asks the server; takes a few seconds on a slow network.
  final Future<String> Function() checkPayment;

  @override
  State<PaymentStatusScreen> createState() => _PaymentStatusScreenState();
}

class _PaymentStatusScreenState extends State<PaymentStatusScreen> {
  String status = 'Checking payment...';

  @override
  void initState() {
    super.initState();
    _check();
  }

  Future<void> _check() async {
    final answer = await widget.checkPayment();
    setState(() => status = answer);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Payment')),
      body: Center(child: Text(status)),
    );
  }
}
