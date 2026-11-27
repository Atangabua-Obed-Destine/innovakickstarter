// Each test replays one crash from CRASH_REPORT.md, with the same data.
// Today they fail with the same errors as the reps' phones.
// Fix the code in lib/ (not these tests) until all of them pass.
// Run:  flutter test
import 'dart:async';

import 'package:material_ui/material_ui.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:nkwen_crash_report/branches.dart';
import 'package:nkwen_crash_report/orders.dart';
import 'package:nkwen_crash_report/payment_status_screen.dart';

Order order(String id, String status, {String branch = 'NKW', int total = 410}) =>
    Order(id: id, branch: branch, customer: 'Mbah Clarisse', status: status, totalFcfa: total);

void main() {
  test('Crash 1: an order taken offline has no total yet', () {
    // Exactly what the app saved while the rep had no network.
    final saved = {'id': 'LOCAL-0007', 'branch': 'CAV', 'customer_name': 'Ndi Grace', 'status': 'waiting_to_sync', 'total_fcfa': null};

    final o = Order.fromJson(saved);

    expect(o.id, 'LOCAL-0007');
    expect(o.totalText, isNot(contains('null')), reason: 'Show something a rep understands, not "null".');
  });

  test('Crash 1 (check): a synced order still shows its total', () {
    final synced = {'id': 'ORD-7Q2K9X1B', 'branch': 'CAV', 'customer_name': 'Mbah Clarisse', 'status': 'paid', 'total_fcfa': 78610};
    expect(Order.fromJson(synced).totalText, contains('78'));
  });

  test('Crash 2: "Clear delivered" with two delivered orders next to each other', () {
    final today = [order('A', 'paid'), order('B', 'delivered'), order('C', 'delivered'), order('D', 'paid')];

    clearDelivered(today);

    expect(today.map((o) => o.id), ['A', 'D']);
  });

  test('Crash 2 (check): delivered orders at the end of the list', () {
    final today = [order('A', 'paid'), order('B', 'delivered'), order('C', 'delivered')];
    clearDelivered(today);
    expect(today.map((o) => o.id), ['A']);
  });

  test('Crash 3: an old order saved with a lower-case branch code', () {
    expect(branchName('ml4'), 'Mile 4');
  });

  test('Crash 3 (check): an unknown code does not crash the order card', () {
    expect(branchName('XYZ'), isNotEmpty);
    expect(branchName('LMB'), 'Limbe Down Beach');
  });

  testWidgets('Crash 4: the rep leaves the payment screen before the answer arrives', (tester) async {
    final answer = Completer<String>();
    await tester.pumpWidget(MaterialApp(home: PaymentStatusScreen(checkPayment: () => answer.future)));
    expect(find.text('Checking payment...'), findsOneWidget);

    await tester.pumpWidget(const MaterialApp(home: Text('Orders'))); // the rep taps Back
    answer.complete('Paid'); // the slow network answers later
    await tester.pump();

    expect(find.text('Orders'), findsOneWidget);
  });
}
