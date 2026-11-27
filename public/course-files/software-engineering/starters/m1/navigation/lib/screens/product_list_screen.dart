import 'package:material_ui/material_ui.dart';

import '../data/branches.dart';
import '../data/customers.dart';
import '../models/customer.dart';
import '../models/product.dart';
import '../widgets/product_list_view.dart';
import 'customer_list_screen.dart';
import 'product_detail_screen.dart';

// The first screen: the product list. It opens two other screens.
class ProductListScreen extends StatefulWidget {
  const ProductListScreen({super.key, required this.products, this.branch = 'NKW'});

  final List<Product> products;
  final String branch;

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  Customer? _visiting;

  Future<void> _openProduct(Product product) async {
    // TODO 1: push a MaterialPageRoute that builds
    //         ProductDetailScreen(product: product, branch: widget.branch).
    //         Use  final added = await Navigator.push<int>(...);
    //         "added" is the quantity the detail screen pops with, or null after Back.
    // TODO 2: if the screen is still mounted and added is not null, show a SnackBar:
    //         Added 3 x Rice 50kg      (use the real quantity and name)
    //         ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(...)));
  }

  Future<void> _chooseCustomer() async {
    // TODO 3: push CustomerListScreen(customers: sampleCustomers) and await a Customer.
    //         If it is null (the rep pressed Back), do nothing.
    //         Otherwise save it in _visiting inside setState().
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Products at ${branchName(widget.branch)}'),
        actions: [
          // TODO 4: an IconButton with tooltip 'Customers', icon Icons.people,
          //         that calls _chooseCustomer.
        ],
      ),
      body: Column(
        children: [
          // TODO 5: when _visiting is not null, show a ListTile with the text
          //         Visiting: Kamga Alimentation      (the real customer name)
          //         Tip: wrap it in Material(color: Theme.of(context).colorScheme.secondaryContainer, ...)
          Expanded(
            child: ProductListView(
              products: widget.products,
              branch: widget.branch,
              onTap: _openProduct,
            ),
          ),
        ],
      ),
    );
  }
}
