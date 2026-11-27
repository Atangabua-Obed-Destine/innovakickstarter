import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';

import 'mb1_cart.dart';
import 'mb1_data.dart';

// One product: name and price, stock at the branch, a quantity and an Add button.
class QuantityRow extends StatefulWidget {
  const QuantityRow({super.key, required this.product, required this.branch});

  final Mb1Product product;
  final String branch;

  @override
  State<QuantityRow> createState() => _QuantityRowState();
}

class _QuantityRowState extends State<QuantityRow> {
  int _quantity = 1;

  @override
  Widget build(BuildContext context) {
    final product = widget.product;
    final stock = product.stock[widget.branch]!;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      child: Row(
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('${product.name} · ${mb1Fcfa(product.priceFcfa)}', style: Theme.of(context).textTheme.titleMedium),
              Text('Stock at ${mb1Branches[widget.branch]}: $stock'),
            ],
          ),
          IconButton(
            tooltip: 'More',
            icon: const Icon(Icons.add),
            onPressed: () {
              _quantity++;
            },
          ),
          Text('$_quantity', key: ValueKey('mb1-qty-${product.id}')),
          IconButton(
            tooltip: 'Add to cart',
            icon: const Icon(Icons.add_shopping_cart),
            onPressed: () => context.read<Mb1Cart>().add(_quantity),
          ),
        ],
      ),
    );
  }
}
