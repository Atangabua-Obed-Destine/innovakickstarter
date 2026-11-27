// Sends the orders saved on the phone, when there is a network.
import '../data/order_store.dart';
import '../services/nkwen_api.dart';
import '../services/order_answer.dart';
import '../services/orders_api.dart';
import 'order_queue.dart';

class SyncReport {
  int sent = 0; // accepted by the server
  int needAttention = 0; // refused (422, 409): the rep must look
  int failed = 0; // no answer or server trouble: will be tried again
  String? problem; // why the run stopped early, if it did

  @override
  String toString() => 'sent $sent, need attention $needAttention, failed $failed';
}

class SyncService {
  SyncService({required this.api, required this.store});

  final OrdersApi api;
  final OrderStore store;

  Future<SyncReport>? _running;

  // TODO 8: if a sync is already running, give back the SAME Future.
  //         Otherwise start _sendAll() and forget it when it completes:
  //         return _running ??= _sendAll().whenComplete(() => _running = null);
  Future<SyncReport> syncNow() {
    return _sendAll();
  }

  Future<SyncReport> _sendAll() async {
    final report = SyncReport();
    // TODO 9: for each order in await store.pendingOrders() (oldest first):
    //   a. answer = await api.postOrder(order.toApiJson(), idempotencyKey: order.localId)
    //      If it throws ApiException: recordFailedAttempt, failed += 1, problem = e.message, and STOP (break).
    //   b. answer.isCreated          -> markSynced(localId, serverId: answer.serverId ?? 'unknown',
    //                                    serverTotalFcfa: answer.serverTotalFcfa, serverStatus: answer.serverStatus); sent += 1
    //   c. 429 or 500 and above      -> recordFailedAttempt; failed += 1; problem; STOP (break)
    //   d. anything else (422, 409)  -> markNeedsAttention(localId, _reason(answer)); needAttention += 1
    return report;
  }

  // Words for the Outbox: the server's message, plus the details it gave.
  // ignore: unused_element
  String _reason(OrderAnswer answer) {
    final body = answer.body;
    if (answer.statusCode == 409 && body['available'] != null) {
      return '${answer.message} Asked for ${body['requested']}, the branch has ${body['available']}.';
    }
    final errors = body['errors'];
    if (errors is Map && errors.isNotEmpty) {
      final first = errors.values.first;
      if (first is List && first.isNotEmpty) return '${answer.message} ${first.first}';
    }
    return answer.message;
  }
}
