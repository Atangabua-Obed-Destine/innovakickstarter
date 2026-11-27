// The display rules from Chapter 4 ("Changing the Page"), now as an ES module:
// each function has "export" in front, so other files can import it.
// If your own Chapter 4 format.js is different, you may paste yours in instead.
// Prices already include VAT (19.25%): we never add it on top.

// 36005 -> "36,005 FCFA"
export function formatFcfa(amount) {
  return amount.toLocaleString('en-US') + ' FCFA';
}

// The customer's stock badge: 'out' (0 or less), 'low' (1 to 5) or 'in' (6 or more).
// (This is NOT the manager's restock alert, which uses reorder_level.)
export function stockStatus(quantity) {
  if (quantity <= 0) return 'out';
  if (quantity <= 5) return 'low';
  return 'in';
}

// The words on the badge.
export function stockText(quantity) {
  const status = stockStatus(quantity);
  if (status === 'out') return 'Out of stock';
  if (status === 'low') return `Only ${quantity} left`;
  return 'In stock';
}
