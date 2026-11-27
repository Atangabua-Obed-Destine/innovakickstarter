// The Chapter 4 display rules, typed (from "TypeScript Essentials").
// Prices already include VAT (19.25%): we never add it on top.
import type { StockStatus } from '../types.ts';

// 36005 -> "36,005 FCFA"
export function formatFcfa(amount: number): string {
  return amount.toLocaleString('en-US') + ' FCFA';
}

// The customer's stock badge (not the manager's restock alert, which uses reorder_level).
export function stockStatus(quantity: number): StockStatus {
  if (quantity <= 0) return 'out';
  if (quantity <= 5) return 'low';
  return 'in';
}

export function stockText(quantity: number): string {
  const status = stockStatus(quantity);
  if (status === 'out') return 'Out of stock';
  if (status === 'low') return `Only ${quantity} left`;
  return 'In stock';
}
