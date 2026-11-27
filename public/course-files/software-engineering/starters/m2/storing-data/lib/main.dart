// The entry point of the Nkwen Traders sales-rep app.
import 'package:material_ui/material_ui.dart';

import 'app.dart';
import 'data/local_db.dart';
import 'services/nkwen_api.dart';

Future<void> main() async {
  // Needed before using plugins (such as sqflite) in main().
  WidgetsFlutterBinding.ensureInitialized();
  final db = await LocalDb.open();
  runApp(NkwenRepApp(api: NkwenApi(), db: db));
}
