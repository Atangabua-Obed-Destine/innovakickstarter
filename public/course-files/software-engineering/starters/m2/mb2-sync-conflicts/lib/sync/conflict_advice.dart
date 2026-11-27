// Turns a sync conflict into advice a sales rep can act on.
import '../format.dart';

class ConflictAdvice {
  const ConflictAdvice({required this.title, required this.message, required this.options});

  final String title;
  final String message;
  final List<String> options; // buttons the Outbox can show, most helpful first
}

// A 409 body: {"message": "Not enough Rice 25kg at this branch.", "product_id": 5, "requested": 10, "available": 5}
// TODO 1: return advice that
//   - has a short title,
//   - has a message with the server's message, the branch name, what was asked and what is available,
//   - offers, in this order: 'Reduce to N' (ONLY when N > 0), 'Order from another branch', 'Remove this product'.
ConflictAdvice adviceFor409(Map<String, dynamic> body, {required String branchName}) {
  return const ConflictAdvice(title: 'TODO', message: 'TODO', options: []);
}

// TODO 2: the phone showed an estimate; the server sets the real price.
//   - return null when the two totals are the same;
//   - otherwise a sentence with both totals (formatFcfa) and the difference with + or -,
//     and what the rep should do.
String? priceChangeNotice({required int estimatedFcfa, required int serverFcfa}) {
  return null;
}
