import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';

import '../screens/order_draft_screen.dart';
import '../state/order_draft.dart';

// The basket icon in the top bar. Its number follows the order draft.
class BasketButton extends StatelessWidget {
  const BasketButton({super.key});

  @override
  Widget build(BuildContext context) {
    // TODO 9: read the item count with context.watch<OrderDraft>().itemCount
    //         (watch, not read: watch redraws this button when the draft changes).
    const count = 0;
    return IconButton(
      tooltip: 'Order draft',
      // TODO 10: wrap the icon in a Badge that shows the count, only when count > 0:
      //          Badge(isLabelVisible: count > 0, label: Text('$count'), child: ...)
      icon: const Icon(Icons.shopping_basket),
      // TODO 11: open OrderDraftScreen with Navigator.push and a MaterialPageRoute.
      onPressed: () {},
    );
  }
}
