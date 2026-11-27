import 'package:material_ui/material_ui.dart';

import 'basket.dart';
import 'money.dart';

/// The screen where a rep builds a customer's basket.
class BasketScreen extends StatefulWidget {
  const BasketScreen({
    super.key,
    required this.products,
    required this.onPlaceOrder,
  });

  final List<Product> products;
  final void Function(Basket basket) onPlaceOrder;

  @override
  State<BasketScreen> createState() => _BasketScreenState();
}

class _BasketScreenState extends State<BasketScreen> {
  final basket = Basket();

  void _add(Product product) {
    final added = basket.add(product);
    if (!added) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Not enough stock: only ${product.stock} ${product.name} at this branch.')),
      );
    }
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('New order')),
      body: ListView(
        children: [
          for (final product in widget.products)
            ListTile(
              title: Text(product.name),
              subtitle: Text('${formatFcfa(product.priceFcfa)} · ${product.stock} in stock'),
              trailing: IconButton(
                key: Key('add-${product.id}'),
                tooltip: 'Add ${product.name}',
                icon: const Icon(Icons.add_circle_outline),
                onPressed: () => _add(product),
              ),
            ),
        ],
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Expanded(
                child: Text(
                  'Basket: ${basket.itemCount} items\nTotal: ${formatFcfa(basket.totalFcfa)}',
                  key: const Key('basket-summary'),
                ),
              ),
              FilledButton(
                onPressed: basket.isEmpty ? null : () => widget.onPlaceOrder(basket),
                child: const Text('Place order'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
