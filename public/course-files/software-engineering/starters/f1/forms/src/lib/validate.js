import { NKWEN } from '../data/nkwen.js';

// Ready for you: "+237 670 00 00 01" -> "670000001".
// Removes spaces and dashes, and the country code +237 (or 237) at the start.
export function cleanPhone(text) {
  const compact = String(text).replace(/[\s-]/g, '');
  return compact.replace(/^\+?237(?=\d{9}$)/, '');
}

// TODO 1: check the answers of the wholesale form. "values" holds TEXT from the
// inputs (quantity is text too), plus callMe (true/false).
// Return an object with one message per field that is wrong, for example
//   { business: 'Enter your business name.', quantity: 'Wholesale orders are 10 to 500 units.' }
// and an EMPTY object {} when everything is fine. The rules:
//   business  required; 2 to 80 characters after trim()
//   phone     required; cleanPhone(phone) must be 9 digits starting with 6
//             (a regular expression such as /^6\d{8}$/ helps)
//   productId required (not '')
//   quantity  a whole number from 10 to 500   (Number(...) and Number.isInteger)
//   payment   one of NKWEN.payment_methods
//   message   optional; at most 300 characters
export function validateRequest(values) {
  throw new Error('TODO 1: write validateRequest() in src/lib/validate.js');
}
