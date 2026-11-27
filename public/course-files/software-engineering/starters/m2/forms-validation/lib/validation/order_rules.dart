// The rules for an order, checked on the phone BEFORE the order is saved.
// They copy the server's rules (POST /orders), so a saved order is not refused
// later with a 422, when the rep has already left the shop.
// Each validator returns null when the value is fine, or a message for the rep.
//
// The server's rules (from its 422 answers):
//   customer_name   required, at least 2 and at most 80 characters
//   items.*.quantity whole number, at least 1, at most 100
//   payment_method  cash, mtn_momo, orange_money or bank_transfer
// Our own rule: mobile money needs a Cameroon mobile number (9 digits, starting with 6).

const minNameLength = 2;
const maxNameLength = 80;
const maxQuantity = 100;

// TODO 1: trim the value. Empty -> a message. Shorter than minNameLength or longer than
//         maxNameLength -> a message that says the limit. Otherwise null.
String? validateCustomerName(String? value) {
  return null;
}

// Mobile-money payments need the customer's phone number.
bool phoneRequiredFor(String paymentMethod) => paymentMethod == 'mtn_momo' || paymentMethod == 'orange_money';

// TODO 2: '+237 600 00 01 01' -> '600000101'.
//   - remove spaces, dots and dashes (Tip: replaceAll(RegExp(r'[\s.\-]'), ''))
//   - remove a leading +237 or 00237, or 237 when 12 digits are left
//   - return null when nothing is left
String? normalisePhone(String? value) {
  return value;
}

// TODO 3: use normalisePhone().
//   - no number: a message if phoneRequiredFor(paymentMethod), otherwise null
//   - a number that is not 9 digits starting with 6: a message (Tip: RegExp(r'^6\d{8}$'))
String? validatePhone(String? value, {required String paymentMethod}) {
  return null;
}

// TODO 4: empty -> message; not a whole number (int.tryParse gives null) -> message;
//         below 1 or above maxQuantity -> message; above [available] (when given) -> a message
//         that says how many the branch has. Otherwise null.
String? validateQuantityText(String? value, {int? available}) {
  return null;
}

// TODO 5: a 422 answer looks like
//   {"message": "...", "errors": {"customer_name": ["..."], "items.0.quantity": ["..."]}}
// Return a map: field -> its FIRST message. If there is no "errors" map, return {}.
Map<String, String> fieldErrorsFrom422(Map<String, dynamic> body) {
  return {};
}
