import 'package:material_ui/material_ui.dart';

import '../models/product.dart';
import 'product_card.dart';

// A scrolling list of ProductCards. It only shows products: it does not load them.
class ProductListView extends StatelessWidget {
  const ProductListView({super.key, required this.products, required this.branch, this.onTap});

  final List<Product> products;
  final String branch;
  final void Function(Product product)? onTap;

  @override
  Widget build(BuildContext context) {
    // TODO 8: if products is empty, return Center(child: Text('No products to show.')).
    // TODO 9: otherwise return a ListView.builder with padding: EdgeInsets.all(8),
    //         itemCount: products.length, and an itemBuilder that returns a ProductCard.
    //         When a card is tapped, call onTap with that product (onTap may be null).
    return Column(children: [for (final p in products) Text(p.name)]);
  }
}
