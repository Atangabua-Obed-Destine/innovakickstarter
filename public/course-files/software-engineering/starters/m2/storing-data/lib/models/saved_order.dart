// An order saved on the phone (a row of "orders" plus its rows of "order_items").
// The phone's job for this order: is it still to be sent? This is NOT the order's
// status on the server (pay_at_counter, awaiting_payment, paid...): that is serverStatus.
enum SyncStatus { pending, synced, needsAttention }

// How a status is written in the database, and back.
const syncStatusText = {
  SyncStatus.pending: 'pending',
  SyncStatus.synced: 'synced',
  SyncStatus.needsAttention: 'needs_attention',
};

SyncStatus syncStatusFromText(String text) =>
    syncStatusText.entries.firstWhere((e) => e.value == text, orElse: () => syncStatusText.entries.first).key;

class SavedItem {
  const SavedItem({required this.productId, required this.name, required this.quantity, required this.unitPriceFcfa});

  final int productId;
  final String name;
  final int quantity;
  final int unitPriceFcfa; // the price the rep saw: an estimate

  int get lineTotalFcfa => unitPriceFcfa * quantity;
}

class SavedOrder {
  const SavedOrder({
    required this.localId,
    required this.branch,
    required this.customerName,
    this.customerPhone,
    required this.paymentMethod,
    required this.items,
    required this.estimatedTotalFcfa,
    required this.createdAt,
    this.syncStatus = SyncStatus.pending,
    this.attempts = 0,
    this.lastError,
    this.serverId,
    this.serverTotalFcfa,
    this.serverStatus,
  });

  final String localId; // UUID made on the phone; also the Idempotency-Key when we send it
  final String branch;
  final String customerName;
  final String? customerPhone; // kept on the phone for the mobile-money step (Chapter 9)
  final String paymentMethod;
  final List<SavedItem> items;
  final int estimatedTotalFcfa;
  final DateTime createdAt;
  final SyncStatus syncStatus;
  final int attempts;
  final String? lastError;
  final String? serverId; // e.g. ORD-7Q2K9X1B, after the server accepted it
  final int? serverTotalFcfa;
  final String? serverStatus; // the Orders API status, e.g. awaiting_payment (Chapter 9 takes payment from it)

  int get itemCount => items.fold(0, (sum, i) => sum + i.quantity);

  // The body for POST /orders. Never send prices: the server sets them.
  Map<String, dynamic> toApiJson() => {
        'branch': branch,
        'customer_name': customerName,
        'payment_method': paymentMethod,
        'items': [
          for (final item in items) {'product_id': item.productId, 'quantity': item.quantity},
        ],
      };

  // Build a SavedOrder from one "orders" row and its "order_items" rows.
  factory SavedOrder.fromRows(Map<String, Object?> row, List<Map<String, Object?>> itemRows) {
    return SavedOrder(
      localId: row['local_id'] as String,
      branch: row['branch'] as String,
      customerName: row['customer_name'] as String,
      customerPhone: row['customer_phone'] as String?,
      paymentMethod: row['payment_method'] as String,
      estimatedTotalFcfa: row['estimated_total_fcfa'] as int,
      createdAt: DateTime.parse(row['created_at'] as String),
      syncStatus: syncStatusFromText(row['sync_status'] as String),
      attempts: row['attempts'] as int,
      lastError: row['last_error'] as String?,
      serverId: row['server_id'] as String?,
      serverTotalFcfa: row['server_total_fcfa'] as int?,
      serverStatus: row['server_status'] as String?,
      items: [
        for (final i in itemRows)
          SavedItem(
            productId: i['product_id'] as int,
            name: i['name'] as String,
            quantity: i['quantity'] as int,
            unitPriceFcfa: i['unit_price_fcfa'] as int,
          ),
      ],
    );
  }
}
