// Chapter 9: one sent order. From here the rep takes the mobile-money payment,
// adds the receipt photo, and (with a real server) refreshes the status.
import 'dart:io';

import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';

import '../data/order_extras.dart';
import '../data/order_store.dart';
import '../device/phone_features.dart';
import '../format.dart';
import '../models/saved_order.dart';
import '../payments/pay_screen.dart';
import '../payments/payment_flow.dart';
import '../payments/sandbox_payment_api.dart';
import '../receipts/receipt_rules.dart';
import '../reminders/delivery_reminders.dart';
import '../services/order_status_api.dart';

/// Only an order the SERVER knows, priced, and waiting for mobile money can be paid.
bool canTakePayment(SavedOrder order) {
  // TODO 1: true only if the order is synced, has a serverId and a serverTotalFcfa,
  // and its serverStatus is 'awaiting_payment'. A draft or an unsent order has no
  // server id, so it can never be paid from the phone.
  throw UnimplementedError('TODO 1: write canTakePayment()');
}

/// The sandbox flow for one order. In Chapter 10, swap SandboxPaymentApi for
/// HttpPaymentApi(baseUrl: ..., token: ...) and set sandbox: false.
PaymentFlow sandboxFlowFor(SavedOrder order) => PaymentFlow(
      api: SandboxPaymentApi(orders: {order.serverId!: order.serverTotalFcfa!}),
      orderId: order.serverId!,
      totalFcfa: order.serverTotalFcfa!,
    );

class OrderDetailScreen extends StatefulWidget {
  const OrderDetailScreen({
    super.key,
    required this.order,
    this.makeFlow = sandboxFlowFor,
    this.takePhoto,
    this.photoBytes,
    this.cancelReminders,
    this.statusApi,
  });

  final SavedOrder order;
  final PaymentFlow Function(SavedOrder order) makeFlow;
  final Future<String?> Function()? takePhoto; // default: the real camera
  final Future<int> Function(String path)? photoBytes; // default: the file's size
  final Future<void> Function(String orderId)? cancelReminders; // default: DeliveryReminders
  final OrderStatusApi? statusApi; // only with a real server (Chapter 10)

  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  late String? status = widget.order.serverStatus;
  String note = '';

  SavedOrder get order => widget.order;
  String get id => order.serverId ?? order.localId;

  Future<void> _pay() async {
    final flow = widget.makeFlow(order);
    await Navigator.push(context, MaterialPageRoute(builder: (context) => PayScreen(flow: flow)));
    if (!mounted) return;
    // TODO 2: ONLY if flow.step is PayStep.paid (the server confirmed):
    //   save 'paid' with context.read<OrderStore>().setServerStatus(order.localId, 'paid'),
    //   check mounted, then setState: status = 'paid', note = 'Paid. The server confirmed it.'
    // Declined, unknown or closed early: change nothing.
  }

  Future<void> _addPhoto() async {
    final take = widget.takePhoto ?? () async => (await takeReceiptPhoto())?.path;
    final size = widget.photoBytes ?? (path) => File(path).length();
    final path = await take();
    if (path == null || !mounted) return; // the rep cancelled: fine
    final problem = receiptProblem(await size(path));
    if (problem != null) {
      if (mounted) setState(() => note = problem);
      return;
    }
    final now = DateTime.now();
    final name = receiptFileName(id, now);
    if (!mounted) return;
    await context.read<OrderStore>().saveReceipt(order.localId, path: path, fileName: name, takenAt: now);
    if (mounted) setState(() => note = 'Receipt saved: $name');
  }

  Future<void> _refresh() async {
    final fresh = await widget.statusApi!.statusOf(order.serverId!);
    if (fresh == null || !mounted) {
      if (mounted) setState(() => note = 'Could not reach the server. The status shown is the last one saved.');
      return;
    }
    // TODO 3: save the fresh status (setServerStatus). If wantsReminders(fresh) is false
    // (delivered or cancelled), cancel the reminders with widget.cancelReminders, or by
    // default DeliveryReminders().cancelFor(id). Then show the fresh status.
    throw UnimplementedError('TODO 3: save the refreshed status and cancel reminders');
  }

  @override
  Widget build(BuildContext context) {
    final total = order.serverTotalFcfa ?? order.estimatedTotalFcfa;
    return Scaffold(
      appBar: AppBar(
        title: Text(order.serverId ?? 'Not sent yet'),
        actions: [
          if (widget.statusApi != null && order.serverId != null)
            IconButton(key: const ValueKey('refresh-status'), tooltip: 'Refresh status', icon: const Icon(Icons.refresh), onPressed: _refresh),
        ],
      ),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        Text(order.customerName, style: Theme.of(context).textTheme.titleLarge),
        Text('Total: ${formatFcfa(total)}'),
        Text('Status: ${status ?? 'waiting to send'}', key: const ValueKey('order-status')),
        const SizedBox(height: 16),
        if (canTakePayment(order) && status == 'awaiting_payment')
          FilledButton.icon(
            key: const ValueKey('take-payment'),
            onPressed: _pay,
            icon: const Icon(Icons.phone_android),
            label: const Text('Take mobile-money payment'),
          ),
        OutlinedButton.icon(
          key: const ValueKey('add-receipt'),
          onPressed: _addPhoto,
          icon: const Icon(Icons.camera_alt),
          label: const Text('Add receipt photo'),
        ),
        if (note.isNotEmpty) Padding(padding: const EdgeInsets.only(top: 12), child: Text(note, key: const ValueKey('note'))),
      ]),
    );
  }
}
