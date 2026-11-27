import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';

import '../data/branches.dart';
import '../data/order_store.dart';
import '../format.dart';
import '../models/saved_order.dart';

// Every order saved on this phone, newest first.
class OutboxScreen extends StatefulWidget {
  const OutboxScreen({super.key});

  @override
  State<OutboxScreen> createState() => _OutboxScreenState();
}

class _OutboxScreenState extends State<OutboxScreen> {
  // TODO 7: like the product list: a late Future<List<SavedOrder>> _orders,
  //         started once in initState with context.read<OrderStore>().allOrders().

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Outbox')),
      // TODO 8: a FutureBuilder<List<SavedOrder>> with four states:
      //   not done -> CircularProgressIndicator
      //   error    -> a short message
      //   empty    -> Text('No orders saved on this phone yet.')
      //   orders   -> a ListView.builder of ListTiles:
      //                title:    the customer name
      //                subtitle: '3 items · Mile 4 · Waiting to send'  (order.itemCount, branchName)
      //                trailing: formatFcfa(order.estimatedTotalFcfa)
      body: const Center(child: Text('TODO: list the saved orders')),
    );
  }
}
