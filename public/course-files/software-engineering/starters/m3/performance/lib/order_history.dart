import 'package:material_ui/material_ui.dart';

/// One past order, as the rep's history screen shows it.
class PastOrder {
  const PastOrder(this.id, this.customer, this.totalFcfa, this.day);
  final String id;
  final String customer;
  final int totalFcfa;
  final DateTime day;
}

/// Counts how much work the screen does. Tests read it; the app ignores it.
class WorkCounter {
  static int describes = 0;
  static int sorts = 0;
  static void reset() => describes = sorts = 0;
}

/// The text under each order. Pretend it is costly (formatting, dates...).
String describe(PastOrder o) {
  WorkCounter.describes++;
  final d = '${o.day.day.toString().padLeft(2, '0')}/${o.day.month.toString().padLeft(2, '0')}/${o.day.year}';
  return '$d · ${o.totalFcfa} FCFA';
}

/// On a bad network the photo may not arrive: show an empty space, not an error.
Widget noPhoto(BuildContext context, Object error, StackTrace? stack) => const SizedBox(height: 64);

/// A rep's order history: often 2,000 orders or more after a few months.
class OrderHistory extends StatefulWidget {
  const OrderHistory({super.key, required this.orders, required this.photoUrl});
  final List<PastOrder> orders;
  final String photoUrl; // the shop's photo: a big file from the server
  @override
  State<OrderHistory> createState() => _OrderHistoryState();
}

class _OrderHistoryState extends State<OrderHistory> {
  bool showTotals = true;

  @override
  Widget build(BuildContext context) {
    // SLOW 1: sorting all orders again on every rebuild.
    WorkCounter.sorts++;
    final sorted = [...widget.orders]..sort((a, b) => b.day.compareTo(a.day));

    return Scaffold(
      appBar: AppBar(
        title: const Text('My orders'),
        actions: [
          Switch(value: showTotals, onChanged: (v) => setState(() => showTotals = v)),
        ],
      ),
      // SLOW 2: building a row for EVERY order, even the 1,990 nobody can see.
      body: ListView(
        children: [
          // SLOW 3: the full-size photo is decoded, even though it is shown 64 pixels wide.
          Image.network(widget.photoUrl, height: 64, errorBuilder: noPhoto),
          for (final o in sorted)
            ListTile(
              title: Text(o.customer),
              subtitle: showTotals ? Text(describe(o)) : null,
              trailing: Text(o.id),
            ),
        ],
      ),
    );
  }
}
