// TODO 2: the Chapter 4 display rules, still in plain JavaScript.
// With "strict" on, TypeScript refuses parameters without a type
// ("Parameter 'amount' implicitly has an 'any' type").
// Add a type to every parameter and a return type to every function.
// stockStatus must return a StockStatus (import it from '../types.ts').

// 36005 -> "36,005 FCFA"
export function formatFcfa(amount) {
  return amount.toLocaleString('en-US') + ' FCFA';
}

export function stockStatus(quantity) {
  if (quantity <= 0) return 'out';
  if (quantity <= 5) return 'low';
  return 'in';
}

export function stockText(quantity) {
  const status = stockStatus(quantity);
  if (status === 'out') return 'Out of stock';
  if (status === 'low') return `Only ${quantity} left`;
  return 'In stock';
}
