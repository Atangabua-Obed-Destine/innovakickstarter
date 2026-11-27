// Self-check for "Forms & Validation on Mobile": the rules, with no screens.
// Run it:  flutter test test/order_rules_test.dart
// Do not change this file.
import 'package:flutter_test/flutter_test.dart';
import 'package:nkwen_rep_app/validation/order_rules.dart';

void main() {
  test('customer name: required, 2 to 80 characters, spaces do not count', () {
    expect(validateCustomerName(''), isNotNull, reason: 'TODO 1.');
    expect(validateCustomerName('   '), isNotNull);
    expect(validateCustomerName('A'), isNotNull, reason: 'The server refuses one letter (422).');
    expect(validateCustomerName(' Ade '), isNull);
    expect(validateCustomerName('B' * 80), isNull);
    expect(validateCustomerName('B' * 81), isNotNull);
    expect(validateCustomerName('A')!, contains('2'), reason: 'Say the limit, so the rep knows what to fix.');
  });

  test('phone numbers are cleaned before they are checked', () {
    expect(normalisePhone('600 00 01 01'), '600000101', reason: 'TODO 2.');
    expect(normalisePhone('+237 600-000-101'), '600000101');
    expect(normalisePhone('00237600000101'), '600000101');
    expect(normalisePhone('237600000101'), '600000101');
    expect(normalisePhone('  '), isNull);
  });

  test('mobile money needs a valid Cameroon mobile number; cash does not', () {
    expect(validatePhone('', paymentMethod: 'mtn_momo'), isNotNull, reason: 'TODO 3: required for mtn_momo.');
    expect(validatePhone('', paymentMethod: 'orange_money'), isNotNull);
    expect(validatePhone('', paymentMethod: 'cash'), isNull);
    expect(validatePhone('600 000 104', paymentMethod: 'mtn_momo'), isNull);
    expect(validatePhone('222 123 456', paymentMethod: 'mtn_momo'), isNotNull, reason: 'Numbers starting with 2 are landlines.');
    expect(validatePhone('6000001', paymentMethod: 'orange_money'), isNotNull, reason: 'Too short.');
    expect(validatePhone('6000001', paymentMethod: 'cash'), isNotNull, reason: 'A number that is typed must be valid, even for cash.');
  });

  test('quantity: a whole number from 1 to 100, and not more than the branch has', () {
    expect(validateQuantityText(''), isNotNull, reason: 'TODO 4.');
    expect(validateQuantityText('two'), isNotNull);
    expect(validateQuantityText('2.5'), isNotNull);
    expect(validateQuantityText('0'), isNotNull);
    expect(validateQuantityText('101'), isNotNull);
    expect(validateQuantityText('100'), isNull);
    expect(validateQuantityText(' 3 ', available: 3), isNull);
    expect(validateQuantityText('5', available: 3), contains('3'), reason: 'Say how many the branch has.');
  });

  test('a 422 answer becomes one message per field', () {
    final body = {
      'message': 'The order is not valid.',
      'errors': {
        'customer_name': ['The customer name field must be at least 2 characters.'],
        'items.0.quantity': ['The items.0.quantity field must be at least 1.', 'second message'],
      },
    };
    expect(fieldErrorsFrom422(body), {
      'customer_name': 'The customer name field must be at least 2 characters.',
      'items.0.quantity': 'The items.0.quantity field must be at least 1.',
    }, reason: 'TODO 5: keep the FIRST message of each field.');
    expect(fieldErrorsFrom422({'message': 'Not enough Rice 25kg at this branch.'}), isEmpty);
  });
}
