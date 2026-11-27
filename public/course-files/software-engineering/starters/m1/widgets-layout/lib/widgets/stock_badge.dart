import 'package:material_ui/material_ui.dart';

import '../format.dart';

// A small coloured label: "In stock", "Only 3 left" or "Out of stock".
class StockBadge extends StatelessWidget {
  const StockBadge({super.key, required this.quantity});

  final int quantity;

  @override
  Widget build(BuildContext context) {
    // TODO 6: return a Container with:
    //  - padding: EdgeInsets.symmetric(horizontal: 8, vertical: 2)
    //  - decoration: a BoxDecoration with a light colour and rounded corners
    //    (borderRadius: BorderRadius.circular(12)).
    //    Choose the colour from stockStatus(quantity): red for 'out', orange for 'low',
    //    green for 'in'. Colors.red.shade100 is a light red; Colors.red.shade900 is dark.
    //  - child: Text(stockText(quantity)) with a small font (fontSize: 12) in the dark colour.
    return const Text('TODO');
  }
}
