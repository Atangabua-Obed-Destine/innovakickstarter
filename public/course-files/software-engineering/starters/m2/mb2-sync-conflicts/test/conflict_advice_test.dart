// Self-check for "Problem Solving MB2: Sync Conflicts".
// Run it:  flutter test test/conflict_advice_test.dart
// These checks test the shape of your advice, not your exact words. Do not change this file.
import 'package:flutter_test/flutter_test.dart';
import 'package:nkwen_rep_app/sync/conflict_advice.dart';

void main() {
  test('409 with some stock left: the numbers, and "Reduce to N" first', () {
    final advice = adviceFor409(
      {'message': 'Not enough Rice 25kg at this branch.', 'product_id': 5, 'requested': 10, 'available': 5},
      branchName: 'Nkwen',
    );
    expect(advice.title, isNot('TODO'), reason: 'TODO 1.');
    expect(advice.message, contains('Nkwen'));
    expect(advice.message, contains('10'));
    expect(advice.message, contains('5'));
    expect(advice.options.first, 'Reduce to 5');
    expect(advice.options, containsAll(['Order from another branch', 'Remove this product']));
  });

  test('409 with nothing left: no "Reduce" option', () {
    final advice = adviceFor409(
      {'message': 'Not enough Plantain (bunch) at this branch.', 'product_id': 19, 'requested': 5, 'available': 0},
      branchName: 'Bafoussam Centre',
    );
    expect(advice.options.any((o) => o.startsWith('Reduce')), isFalse, reason: 'You cannot reduce to 0.');
    expect(advice.options.first, 'Order from another branch');
    expect(advice.message, contains('Bafoussam Centre'));
  });

  test('no price notice when the totals agree', () {
    expect(priceChangeNotice(estimatedFcfa: 72010, serverFcfa: 72010), isNull);
  });

  test('a price notice shows both totals and the difference with its sign', () {
    final up = priceChangeNotice(estimatedFcfa: 150800, serverFcfa: 154800);
    expect(up, isNotNull, reason: 'TODO 2.');
    expect(up, contains('150,800 FCFA'));
    expect(up, contains('154,800 FCFA'));
    expect(up, contains('+4,000 FCFA'));
    final down = priceChangeNotice(estimatedFcfa: 7600, serverFcfa: 7000);
    expect(down, contains('-600 FCFA'));
  });
}
