// Your first screen: a counter of the shops a sales rep visited today.
// It is the counter app that "flutter create" made, changed for Nkwen Traders.
import 'package:material_ui/material_ui.dart';

class VisitsScreen extends StatefulWidget {
  const VisitsScreen({super.key});

  // A rep should visit this many shops a day.
  static const dailyTarget = 8;

  @override
  State<VisitsScreen> createState() => _VisitsScreenState();
}

class _VisitsScreenState extends State<VisitsScreen> {
  int _visits = 0;

  void _addVisit() {
    // TODO 1: add 1 to _visits INSIDE setState(() { ... }), so the screen redraws.
  }

  void _reset() {
    // TODO 2: set _visits back to 0, also inside setState.
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        // TODO 3: the title must say: Nkwen Rep
        title: const Text('Flutter Demo'),
        actions: [
          IconButton(
            tooltip: 'Reset',
            icon: const Icon(Icons.restart_alt),
            onPressed: _reset,
          ),
        ],
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text('Shops visited today'),
            Text(
              '$_visits',
              style: Theme.of(context).textTheme.displayMedium,
            ),
            const Text('Target: ${VisitsScreen.dailyTarget} shops'),
            // TODO 4: when _visits is 8 or more, also show the text
            //         Target reached. Well done!
            // Hint: inside a list you can write  if (condition) const Text('...'),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        // TODO 5: the tooltip must say: Add visit   (and choose a shop icon, e.g. Icons.storefront)
        tooltip: 'Increment',
        onPressed: _addVisit,
        child: const Icon(Icons.add),
      ),
    );
  }
}
