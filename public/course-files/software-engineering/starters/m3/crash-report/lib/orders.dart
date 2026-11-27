/// Orders as the rep's app stores them. Some come from the server;
/// some were taken offline and are waiting to sync (the server has not
/// priced them yet).
class Order {
  Order({required this.id, required this.branch, required this.customer, required this.status, required this.totalFcfa});

  final String id;
  final String branch;
  final String customer;
  final String status;
  final int totalFcfa;

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'] as String,
      branch: json['branch'] as String,
      customer: json['customer_name'] as String,
      status: json['status'] as String,
      totalFcfa: json['total_fcfa'] as int,
    );
  }

  /// What the order list shows under the customer's name.
  String get totalText => '$totalFcfa FCFA';
}

/// Removes delivered orders from today's list (the rep swipes "Clear delivered").
void clearDelivered(List<Order> today) {
  final count = today.length;
  for (var i = 0; i < count; i++) {
    if (today[i].status == 'delivered') today.removeAt(i);
  }
}
