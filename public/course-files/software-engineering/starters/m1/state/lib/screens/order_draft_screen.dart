import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';

import '../data/branches.dart';
import '../format.dart';
import '../state/order_draft.dart';

// The order the rep is building: customer, lines and an estimated total.
class OrderDraftScreen extends StatelessWidget {
  const OrderDraftScreen({super.key});

  @override
  Widget build(BuildContext context) {
    // TODO 12: get the draft with context.watch<OrderDraft>() so this screen redraws on every change.
    final draft = OrderDraft();
    final text = Theme.of(context).textTheme;
    final customer = draft.customer;
    return Scaffold(
      appBar: AppBar(title: const Text('Order draft')),
      body: ListView(
        padding: const EdgeInsets.all(8),
        children: [
          ListTile(
            leading: const Icon(Icons.store),
            title: Text(customer == null ? 'No customer chosen' : 'Customer: ${customer.name}'),
            subtitle: Text('From ${branchName(draft.branch)}'),
          ),
          const Divider(),
          if (draft.isEmpty)
            const Padding(
              padding: EdgeInsets.all(24),
              child: Text('No items yet. Add products from the list.', textAlign: TextAlign.center),
            ),
          // TODO 13: for every line in draft.lines, show a Card with two rows:
          //   Row 1: Expanded(Column[name (titleMedium), '36,005 FCFA each' (bodySmall)]),
          //          then the line total with formatFcfa(line.lineTotalFcfa) in bold.
          //   Row 2 (mainAxisAlignment: end):
          //          IconButton tooltip 'Less'   -> draft.setQuantity(id, quantity - 1)
          //          Text('${line.quantity}', key: ValueKey('qty-${line.product.id}'))
          //          IconButton tooltip 'More'   -> draft.setQuantity(id, quantity + 1)
          //          IconButton tooltip 'Remove' -> draft.remove(id)
        ],
      ),
      bottomNavigationBar: draft.isEmpty
          ? null
          : SafeArea(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text('Estimated total: ${formatFcfa(draft.totalFcfa)}', style: text.titleLarge),
                    const Text('Prices include VAT (19.25%). The server confirms the final total.'),
                  ],
                ),
              ),
            ),
    );
  }
}
