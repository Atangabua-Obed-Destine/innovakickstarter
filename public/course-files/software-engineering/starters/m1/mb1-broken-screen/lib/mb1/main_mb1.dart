// Run the broken screen on its own:  flutter run -t lib/mb1/main_mb1.dart
import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';

import 'mb1_cart.dart';
import 'quick_order_screen.dart';

void main() {
  runApp(const Mb1App());
}

class Mb1App extends StatelessWidget {
  const Mb1App({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (context) => Mb1Cart(),
      child: MaterialApp(
        title: 'Quick order',
        theme: ThemeData(colorScheme: ColorScheme.fromSeed(seedColor: Colors.green)),
        home: const QuickOrderScreen(),
      ),
    );
  }
}
