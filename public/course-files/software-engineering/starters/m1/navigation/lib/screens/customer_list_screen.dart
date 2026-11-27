import 'package:material_ui/material_ui.dart';

import '../models/customer.dart';

// A list of customers. Tapping one closes the screen and gives that customer back.
class CustomerListScreen extends StatelessWidget {
  const CustomerListScreen({super.key, required this.customers});

  final List<Customer> customers;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Choose a customer')),
      // TODO 9: a ListView.builder with one ListTile per customer:
      //   leading:  CircleAvatar with the first letter of the name
      //   title:    the name
      //   subtitle: 'Bafoussam · Wholesale'   (town, then Wholesale or Walk-in; see isWholesale)
      //   onTap:    close this screen and give back the customer: Navigator.pop(context, customer)
      body: const Center(child: Text('TODO: list the customers')),
    );
  }
}
