// The whole app: the shared objects (providers) and the first screen.
// Updated in "Storing Data on the Phone": the app now also gets the database.
import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import 'package:sqflite/sqflite.dart';

import 'data/order_store.dart';
import 'data/product_cache.dart';
import 'screens/product_list_screen.dart';
import 'services/nkwen_api.dart';
import 'state/order_draft.dart';

class NkwenRepApp extends StatelessWidget {
  const NkwenRepApp({super.key, required this.api, required this.db});

  final NkwenApi api;
  final Database db;

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<NkwenApi>.value(value: api),
        Provider(create: (context) => OrderStore(db)),
        Provider(create: (context) => ProductCache(db)),
        ChangeNotifierProvider(create: (context) => OrderDraft()),
      ],
      child: MaterialApp(
        title: 'Nkwen Rep',
        theme: ThemeData(colorScheme: ColorScheme.fromSeed(seedColor: Colors.green)),
        home: const ProductListScreen(),
      ),
    );
  }
}
