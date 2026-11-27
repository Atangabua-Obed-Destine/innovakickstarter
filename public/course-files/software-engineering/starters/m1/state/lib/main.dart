// The entry point of the Nkwen Traders sales-rep app.
import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';

import 'data/sample_products.dart';
import 'screens/product_list_screen.dart';
import 'state/order_draft.dart';

void main() {
  runApp(
    // One OrderDraft for the whole app. Every screen below can watch or read it.
    ChangeNotifierProvider(
      create: (context) => OrderDraft(),
      child: const NkwenRepApp(),
    ),
  );
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
      home: const ProductListScreen(products: sampleProducts),
    );
  }
}
