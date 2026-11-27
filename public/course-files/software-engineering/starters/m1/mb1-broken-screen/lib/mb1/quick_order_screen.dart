import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';

import 'mb1_cart.dart';
import 'mb1_data.dart';
import 'quantity_row.dart';

// Quick order: pick a branch, set quantities, add to the cart.
class QuickOrderScreen extends StatefulWidget {
  const QuickOrderScreen({super.key});

  @override
  State<QuickOrderScreen> createState() => _QuickOrderScreenState();
}

class _QuickOrderScreenState extends State<QuickOrderScreen> {
  String _branch = 'NKW';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Quick order'),
        actions: const [CartCount()],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: DropdownButton<String>(
              value: _branch,
              isExpanded: true,
              items: [
                for (final entry in mb1Branches.entries)
                  DropdownMenuItem(value: entry.key, child: Text(entry.value)),
              ],
              onChanged: (code) => setState(() => _branch = code!),
            ),
          ),
          ListView.builder(
            itemCount: mb1Products.length,
            itemBuilder: (context, index) => QuantityRow(product: mb1Products[index], branch: _branch),
          ),
        ],
      ),
    );
  }
}

// The number of items in the cart, in the top bar.
class CartCount extends StatelessWidget {
  const CartCount({super.key});

  @override
  Widget build(BuildContext context) {
    final count = context.read<Mb1Cart>().count;
    return Center(
      child: Padding(
        padding: const EdgeInsets.only(right: 16),
        child: Text('Cart: $count'),
      ),
    );
  }
}
