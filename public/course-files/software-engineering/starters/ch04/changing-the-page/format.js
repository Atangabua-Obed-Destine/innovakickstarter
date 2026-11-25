// Small helpers for showing prices and stock. They do not touch the page,
// so Node can test them. Run the tests with:  node --test

// TODO 1: 36005 -> "36,005 FCFA"
// Hint: numbers have a toLocaleString('en-US') method.
function formatFcfa(amount) {
  throw new Error('TODO: write formatFcfa()');
}

// TODO 2: return 'out' when quantity is 0 or less,
// 'low' when it is 1 to 5, and 'in' when it is 6 or more.
function stockStatus(quantity) {
  throw new Error('TODO: write stockStatus()');
}

// TODO 3: the words on the stock badge:
// 'Out of stock', 'Only 3 left' (use the real number) or 'In stock'.
// Use stockStatus() so the rule lives in one place.
function stockText(quantity) {
  throw new Error('TODO: write stockText()');
}

// This line lets Node's test runner use the functions. The browser ignores it.
if (typeof module !== 'undefined') module.exports = { formatFcfa, stockStatus, stockText };
