// Chapter 9 additions to the phone's database, without changing LocalDb:
// - the order's server status after a payment or a refresh;
// - receipt photos, in their own small table (created the first time it is needed).
import '../data/order_store.dart';

extension OrderExtras on OrderStore {
  Future<void> setServerStatus(String localId, String status) {
    return db.rawUpdate('UPDATE orders SET server_status = ? WHERE local_id = ?', [status, localId]);
  }

  Future<void> _receiptsTable() => db.execute(
      'CREATE TABLE IF NOT EXISTS receipts (local_id TEXT PRIMARY KEY, path TEXT NOT NULL, file_name TEXT NOT NULL, taken_at TEXT NOT NULL)');

  Future<void> saveReceipt(String localId, {required String path, required String fileName, required DateTime takenAt}) async {
    await _receiptsTable();
    await db.rawInsert('INSERT OR REPLACE INTO receipts (local_id, path, file_name, taken_at) VALUES (?, ?, ?, ?)',
        [localId, path, fileName, takenAt.toIso8601String()]);
  }

  Future<String?> receiptFileNameFor(String localId) async {
    await _receiptsTable();
    final rows = await db.query('receipts', where: 'local_id = ?', whereArgs: [localId]);
    return rows.isEmpty ? null : rows.first['file_name'] as String;
  }
}
