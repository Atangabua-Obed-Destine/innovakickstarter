// The entry point of the Nkwen Traders sales-rep app.
import 'package:material_ui/material_ui.dart';

import 'app.dart';
import 'data/local_db.dart';
import 'services/nkwen_api.dart';
import 'services/orders_api.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final db = await LocalDb.open();
  runApp(NkwenRepApp(api: NkwenApi(), ordersApi: OrdersApi(), db: db));
}
