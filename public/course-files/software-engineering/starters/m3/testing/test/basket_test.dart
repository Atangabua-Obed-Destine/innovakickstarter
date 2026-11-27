// Unit tests: they check one class or function, with no screen.
// Run them with:  flutter test test/basket_test.dart
import 'package:flutter_test/flutter_test.dart';
import 'package:nkwen_basket_tests/basket.dart';
import 'package:nkwen_basket_tests/money.dart';

// Test data: real Nkwen products, with their stock at the Nkwen branch.
const rice25 = Product(id: 5, name: 'Rice 25kg', priceFcfa: 19055, stock: 5);
const rice50 = Product(id: 6, name: 'Rice 50kg', priceFcfa: 36005, stock: 42);
const palmOil5 = Product(id: 15, name: 'Palm Oil 5L', priceFcfa: 6600, stock: 15);

void main() {
  // A finished example. Read it first: Arrange, Act, Assert.
  test('a new basket is empty and costs nothing', () {
    final basket = Basket(); // Arrange

    final total = basket.totalFcfa; // Act

    expect(total, 0); // Assert
    expect(basket.isEmpty, isTrue);
    expect(basket.itemCount, 0);
  });

  test('2 x Rice 50kg and 1 x Palm Oil 5L cost the same as the Orders API says', () {
    // TODO: add 2 x rice50 and 1 x palmOil5, then check totalFcfa.
    // Work the number out yourself from the prices above (look at ORDERS_API in the lesson).
    // Also check itemCount and lines.length.
    fail('TODO: write the order total test');
  });

  test('adding the same product twice gives ONE line with quantity 2', () {
    // TODO: add rice50, then add rice50 again.
    // Check: lines.length is 1, and that line's quantity is 2.
    fail('TODO: write the "same product twice" test');
  });

  test('cannot add more than the branch has, even in two steps', () {
    // TODO: Rice 25kg has only 5 bags at Nkwen.
    // 1. add(rice25, quantity: 6) must return false and leave the basket empty.
    // 2. add 3, then try to add 3 more: the second add must return false,
    //    and the basket must still hold exactly 3.
    fail('TODO: write the stock limit test');
  });

  test('setting a quantity to 0 removes the line', () {
    // TODO: add rice50 and palmOil5, set rice50's quantity to 0.
    // Check: one line is left, and it is Palm Oil 5L.
    fail('TODO: write the remove test');
  });

  test('formatFcfa writes francs the Nkwen way', () {
    // TODO: check at least four values: 0, 100, 36005 and 1000000.
    // Decide what each one should look like BEFORE you run the test.
    fail('TODO: write the formatFcfa test');
  });
}
