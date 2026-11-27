import 'package:material_ui/material_ui.dart';

import 'basket.dart';
import 'basket_screen.dart';
import 'money.dart';

/// Five products with their stock at the Nkwen branch (NKW), from nkwen.json.
const nkwProducts = [
  Product(id: 5, name: 'Rice 25kg', priceFcfa: 19055, stock: 5),
  Product(id: 6, name: 'Rice 50kg', priceFcfa: 36005, stock: 42),
  Product(id: 9, name: 'Sugar 1kg', priceFcfa: 760, stock: 63),
  Product(id: 13, name: 'Soap (bar)', priceFcfa: 410, stock: 31),
  Product(id: 15, name: 'Palm Oil 5L', priceFcfa: 6600, stock: 15),
];

void main() => runApp(const RepApp());

class RepApp extends StatelessWidget {
  const RepApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Nkwen Rep',
      theme: ThemeData(colorSchemeSeed: Colors.green),
      home: Builder(
        builder: (context) => BasketScreen(
          products: nkwProducts,
          onPlaceOrder: (basket) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text('Order ready to send: ${formatFcfa(basket.totalFcfa)}')),
            );
          },
        ),
      ),
    );
  }
}
