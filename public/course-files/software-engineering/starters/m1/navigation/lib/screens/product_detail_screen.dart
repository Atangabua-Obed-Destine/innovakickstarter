import 'dart:math';

import 'package:material_ui/material_ui.dart';

import '../data/branches.dart';
import '../format.dart';
import '../models/product.dart';

// One product, with its stock at every open branch and a quantity to add.
class ProductDetailScreen extends StatefulWidget {
  const ProductDetailScreen({super.key, required this.product, required this.branch});

  final Product product;
  final String branch; // the branch the order will come from

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  int _quantity = 1;

  int get _available => widget.product.stockAt(widget.branch);

  // The Orders API refuses more than 100 of one product, and the branch
  // cannot give more than it has.
  int get _maxQuantity => min(_available, 100);

  @override
  Widget build(BuildContext context) {
    final product = widget.product;
    final text = Theme.of(context).textTheme;
    return Scaffold(
      appBar: AppBar(title: Text(product.name)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(formatFcfa(product.priceFcfa), style: text.headlineMedium),
          const SizedBox(height: 4),
          Text('${product.category} · ${product.unit} · ${product.sku}'),
          const Text('Price includes VAT (19.25%).'),
          const SizedBox(height: 24),
          Text('Stock by branch', style: text.titleMedium),
          // TODO 6: for every code in openBranchCodes, show a ListTile whose title is
          //         'Nkwen: 42'  (use branchName(code) and product.stockAt(code)).
          //         Mark the order's branch with selected: code == widget.branch.
          //         Tip: inside a list you can write  for (final code in openBranchCodes) ListTile(...),
          const SizedBox(height: 24),
          // TODO 7: if _available is 0, show the text
          //           Out of stock at Mile 4        (use branchName(widget.branch))
          //         otherwise show a Row with:
          //           - IconButton(tooltip: 'Less', icon: Icon(Icons.remove)): takes 1 away, never below 1
          //           - Text('$_quantity', key: const ValueKey('quantity'))
          //           - IconButton(tooltip: 'More', icon: Icon(Icons.add)): adds 1, never above _maxQuantity
          //         Change _quantity inside setState(). Give onPressed: null to disable a button.
          const SizedBox(height: 16),
          FilledButton.icon(
            // TODO 8: disable the button (onPressed: null) when _available is 0.
            //         Otherwise close this screen and give back the quantity:
            //         Navigator.pop(context, _quantity)
            onPressed: () {},
            icon: const Icon(Icons.add_shopping_cart),
            label: const Text('Add to order'),
          ),
        ],
      ),
    );
  }
}
