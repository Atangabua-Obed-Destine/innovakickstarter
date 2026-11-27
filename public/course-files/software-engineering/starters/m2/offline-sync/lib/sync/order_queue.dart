// The queries the sync needs, added to OrderStore with an "extension"
// (extra methods for a class, written in another file).
import '../data/order_store.dart';
import '../models/saved_order.dart';

extension OrderQueue on OrderStore {
  // TODO 4: orders with sync_status 'pending', OLDEST first (orderBy: 'created_at ASC, rowid ASC').
  //         Query 'orders', then use find(localId) to get each SavedOrder with its items.
  Future<List<SavedOrder>> pendingOrders() async {
    return [];
  }

  // TODO 5: the server accepted the order. One UPDATE: sync_status 'synced', server_id, server_total_fcfa,
  //         server_status, synced_at (now, as ISO 8601 text), last_error NULL, attempts + 1.
  //         Tip: db.rawUpdate('UPDATE orders SET sync_status = ?, ... WHERE local_id = ?', [values...])
  Future<void> markSynced(String localId, {required String serverId, int? serverTotalFcfa, String? serverStatus, DateTime? now}) async {}

  // TODO 6: the server refused the order (422 or 409). sync_status 'needs_attention', last_error, attempts + 1.
  Future<void> markNeedsAttention(String localId, String message) async {}

  // TODO 7: no answer, or the server is in trouble. Keep sync_status 'pending'; set last_error; attempts + 1.
  Future<void> recordFailedAttempt(String localId, String message) async {}
}
