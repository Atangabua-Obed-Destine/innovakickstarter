// Rules for the "Call me back" form. No page code here, so Node can test it.
// Run the tests in this folder with:  node --test

const MESSAGE_LIMIT = 200;

// TODO 1: turn what people type into 9 plain digits.
//   "677 12 34 56"      -> "677123456"   (remove spaces, dots and dashes)
//   "+237 677123456"    -> "677123456"   (remove the country code +237)
//   "00237677123456"    -> "677123456"   (00237 is another way to write +237)
//   "237677123456"      -> "677123456"   (only when 12 digits long)
function normalisePhone(text) {
  throw new Error('TODO: write normalisePhone()');
}

// TODO 2: true when the number is a Cameroon mobile number:
// after normalisePhone, exactly 9 digits, and the first one is 6.
// (Real numbering rules have more detail. This is enough for a call-back form.)
function isValidCameroonPhone(text) {
  throw new Error('TODO: write isValidCameroonPhone()');
}

// TODO 3: how many characters are left before the limit (can be negative).
function charactersLeft(text, limit = MESSAGE_LIMIT) {
  throw new Error('TODO: write charactersLeft()');
}

// TODO 4: check a form like { name, phone, branch, message }.
// Return an object with one message per problem, for example
//   { phone: 'Enter a mobile number: 9 digits starting with 6.' }
// Rules: name has at least 2 letters (after trim), phone is valid,
// a branch is chosen, the message is not over the limit.
// Return {} (an empty object) when everything is fine.
function validateCallback(form) {
  throw new Error('TODO: write validateCallback()');
}

// This line lets Node's test runner use the functions. The browser ignores it.
if (typeof module !== 'undefined') module.exports = { MESSAGE_LIMIT, normalisePhone, isValidCameroonPhone, charactersLeft, validateCallback };
