// The Chapter 4 helpers from format.js, now in TypeScript.

// 36005 -> "36,005 FCFA"
export function formatFcfa(amount: number): string {
  return amount.toLocaleString('en-US') + ' FCFA'
}

// How much stock is left: 'out' (0 or less), 'low' (1 to 5) or 'in' (6 or more).
export function stockStatus(quantity: number): 'out' | 'low' | 'in' {
  if (quantity <= 0) return 'out'
  if (quantity <= 5) return 'low'
  return 'in'
}

// The words a customer sees on the stock badge.
export function stockText(quantity: number): string {
  const status = stockStatus(quantity)
  if (status === 'out') return 'Out of stock'
  if (status === 'low') return `Only ${quantity} left`
  return 'In stock'
}
