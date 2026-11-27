// The whole app: the shared objects (providers) and the first screen.
import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';

import 'screens/product_list_screen.dart';
import 'services/nkwen_api.dart';
import 'state/order_draft.dart';

class NkwenRepApp extends StatelessWidget {
  const NkwenRepApp({super.key, required this.api});

  // The API client comes from outside, so tests can give a fake one.
  final NkwenApi api;

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<NkwenApi>.value(value: api),
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
