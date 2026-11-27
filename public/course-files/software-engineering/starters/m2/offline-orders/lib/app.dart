// The whole app: the shared objects (providers) and the first screen.
// Updated in "Project: Offline Order Taking": orders API, sync, and a sync when the app opens.
import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import 'package:sqflite/sqflite.dart';

import 'data/order_store.dart';
import 'data/product_cache.dart';
import 'screens/product_list_screen.dart';
import 'services/nkwen_api.dart';
import 'services/orders_api.dart';
import 'state/order_draft.dart';
import 'sync/sync_service.dart';

class NkwenRepApp extends StatefulWidget {
  const NkwenRepApp({super.key, required this.api, required this.ordersApi, required this.db, this.syncOnStart = true});

  final NkwenApi api;
  final OrdersApi ordersApi;
  final Database db;
  final bool syncOnStart;

  @override
  State<NkwenRepApp> createState() => _NkwenRepAppState();
}

class _NkwenRepAppState extends State<NkwenRepApp> {
  late final OrderStore _store = OrderStore(widget.db);
  late final SyncService _sync = SyncService(api: widget.ordersApi, store: _store);

  @override
  void initState() {
    super.initState();
    // Orders saved yesterday with no network: try to send them as soon as the app opens.
    if (widget.syncOnStart) _sync.syncNow();
  }

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<NkwenApi>.value(value: widget.api),
        Provider<OrderStore>.value(value: _store),
        Provider(create: (context) => ProductCache(widget.db)),
        Provider<SyncService>.value(value: _sync),
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
