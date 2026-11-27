// The entry point of the Nkwen Traders sales-rep app.
// Flutter starts here: main() calls runApp() with the first widget.
import 'package:material_ui/material_ui.dart';

import 'screens/visits_screen.dart';

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
      home: const VisitsScreen(),
    );
  }
}
