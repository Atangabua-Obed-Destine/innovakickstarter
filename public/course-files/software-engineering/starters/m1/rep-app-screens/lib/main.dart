// The entry point of the Nkwen Traders sales-rep app.
import 'package:material_ui/material_ui.dart';

import 'app.dart';
import 'services/nkwen_api.dart';

void main() {
  runApp(NkwenRepApp(api: NkwenApi()));
}
