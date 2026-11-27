// Orders saved on the phone.
import 'package:sqflite/sqflite.dart';
import 'package:uuid/uuid.dart';

import '../models/saved_order.dart';
import '../state/order_draft.dart';

class OrderStore {
  OrderStore(this.db, {Uuid? uuid}) : _uuid = uuid ?? const Uuid();

  final Database db;
  final Uuid _uuid;

  // Save the draft as a new order with status 'pending'. The order and its lines
  // are written in ONE transaction: all of it is saved, or none of it.
  Future<SavedOrder> saveDraft(OrderDraft draft, {required String customerName, String? customerPhone, DateTime? now}) async {
    if (draft.isEmpty) {
      throw ArgumentError('An order needs at least one line.');
    }
    final localId = _uuid.v4(); // made on the phone, so it works with no network
    final createdAt = (now ?? DateTime.now()).toIso8601String();
    // TODO 5: in ONE db.transaction, insert:
    //   - one row into 'orders': local_id, branch, customer_name, customer_phone, payment_method,
    //     estimated_total_fcfa (draft.totalFcfa), created_at, sync_status (syncStatusText[SyncStatus.pending])
    //   - one row into 'order_items' per line: local_id, product_id, name, quantity, unit_price_fcfa

    return (await find(localId))!;
  }

  Future<SavedOrder?> find(String localId) async {
    final rows = await db.query('orders', where: 'local_id = ?', whereArgs: [localId]);
    if (rows.isEmpty) return null;
    return _withItems(rows.first);
  }

  // Newest first, for the Outbox screen.
  Future<List<SavedOrder>> allOrders() async {
    // TODO 6: query every row of 'orders', newest first (orderBy: 'created_at DESC, rowid DESC'),
    //         and turn each row into a SavedOrder with _withItems(row).
    return [];
  }

  Future<SavedOrder> _withItems(Map<String, Object?> row) async {
    final items = await db.query('order_items', where: 'local_id = ?', whereArgs: [row['local_id']], orderBy: 'rowid');
    return SavedOrder.fromRows(row, items);
  }
}
