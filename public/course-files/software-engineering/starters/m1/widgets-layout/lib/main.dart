// The entry point of the Nkwen Traders sales-rep app.
import 'package:material_ui/material_ui.dart';

import 'data/sample_products.dart';
import 'widgets/product_list_view.dart';

void main() {
  runApp(const NkwenRepApp());
}

class NkwenRepApp extends StatelessWidget {
  const NkwenRepApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Nkwen Rep',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.green),
      ),
      home: Scaffold(
        appBar: AppBar(title: const Text('Products at Nkwen')),
        body: ProductListView(products: sampleProducts, branch: 'NKW', onTap: (product) {}),
      ),
    );
  }
}
