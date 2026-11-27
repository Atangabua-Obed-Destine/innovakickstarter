import 'package:material_ui/material_ui.dart';

import '../format.dart';
import '../models/product.dart';
import 'stock_badge.dart';

// One product in the list. Build this layout:
//
//  [G]  Rice 50kg               36,005 FCFA
//       Grains · bag              In stock
//
// Card > InkWell (so it can be tapped) > Padding (all 12) > Row with:
//   1. CircleAvatar showing the first letter of the category: Text(product.category[0])
//   2. SizedBox(width: 12)
//   3. Expanded > Column (crossAxisAlignment: CrossAxisAlignment.start) with
//        - the name, style titleMedium, maxLines: 2, overflow: TextOverflow.ellipsis
//        - SizedBox(height: 4)
//        - Text('${product.category} · ${product.unit}') in a small style (bodySmall)
//   4. SizedBox(width: 8)
//   5. Column (crossAxisAlignment: CrossAxisAlignment.end) with
//        - the price with formatFcfa(), in bold
//        - SizedBox(height: 4)
//        - StockBadge(quantity: product.stockAt(branch))
// Tip: Theme.of(context).textTheme.titleMedium gives you the app's text styles.
class ProductCard extends StatelessWidget {
  const ProductCard({super.key, required this.product, required this.branch, this.onTap});

  final Product product;
  final String branch; // whose stock to show, for example 'NKW'
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    // TODO 7: build the layout above. Give onTap to the InkWell.
    return Card(child: Text(product.name));
  }
}
