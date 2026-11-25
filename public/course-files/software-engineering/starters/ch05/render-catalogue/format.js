// format.js: from Chapter 4 ("Changing the Page"). Use your own copy if you have it.
// Small helpers for showing prices and stock. They do not touch the page,
// so Node can test them.

// 36005 -> "36,005 FCFA"
function formatFcfa(amount) {
  return amount.toLocaleString('en-US') + ' FCFA';
}

// How much stock is left: 'out' (0 or less), 'low' (1 to 5) or 'in' (6 or more).
function stockStatus(quantity) {
  if (quantity <= 0) return 'out';
  if (quantity <= 5) return 'low';
  return 'in';
}

// The words a customer sees on the stock badge.
function stockText(quantity) {
  const status = stockStatus(quantity);
  if (status === 'out') return 'Out of stock';
  if (status === 'low') return `Only ${quantity} left`;
  return 'In stock';
}

// This line lets Node's test runner use the functions. The browser ignores it.
if (typeof module !== 'undefined') module.exports = { formatFcfa, stockStatus, stockText };
