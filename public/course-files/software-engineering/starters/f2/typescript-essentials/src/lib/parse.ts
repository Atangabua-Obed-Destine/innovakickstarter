import type { BranchCode, Product } from '../types.ts';

// Types disappear when the code runs. If the API (or an old file) sends a price
// as text, TypeScript cannot stop it. So data from OUTSIDE your code arrives as
// "unknown", and you check it before you trust it.

// TODO 4a: a "type guard". Return true only for the five real codes
// ('NKW', 'ML4', 'CAV', 'BAF', 'LMB'). Because the return type is
// "value is BranchCode", TypeScript then treats value as a BranchCode.
export function isBranchCode(value: string): value is BranchCode {
  throw new Error(`TODO 4a: isBranchCode(${value})`);
}

// TODO 4b: return a Product when data has the right shape, otherwise null.
// The rules (the same as Problem Solving 105 in Chapter 5):
//   id, price_fcfa, reorder_level: whole numbers, 0 or more (price_fcfa above 0)
//   sku, name, category, unit: text that is not empty
//   stock: an object whose keys are branch codes and whose values are whole numbers 0 or more
// Start with:  if (typeof data !== 'object' || data === null) return null;
// then:        const d = data as Record<string, unknown>;   and check d.id, d.name, ...
export function parseProduct(data: unknown): Product | null {
  throw new Error(`TODO 4b: parseProduct(${typeof data})`);
}
