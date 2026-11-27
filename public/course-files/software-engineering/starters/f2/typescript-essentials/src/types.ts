// TODO 1: describe Nkwen Traders' data with types.
// Replace every "unknown" with a precise type, and finish the interfaces.
// Check your work with:  npm run typecheck   (src/types.check.ts tests these types)

// The four open branch codes, as a union of text literals: 'NKW' | ...
export type OpenBranchCode = unknown;
// The open codes plus 'LMB' (Limbe, planned for 2027).
export type BranchCode = unknown;
// cash, mtn_momo, orange_money, bank_transfer
export type PaymentMethod = unknown;
// The three badge levels from format.js: 'out', 'low', 'in'
export type StockStatus = unknown;

export interface Product {
  id: number;
  name: string;
  // TODO: sku, category, unit, price_fcfa, reorder_level.
  // stock maps a branch code to a number, and Limbe has no entry yet.
  // Use: Partial<Record<BranchCode, number>>
}

export interface Branch {
  code: BranchCode;
  name: string;
  // TODO: city, region, manager, opened_year, and status ('open' or 'planned')
}

// One basket line, { id: 6, qty: 2 }, and a whole basket (an array of lines).
export interface BasketLine {
  id: unknown;
  qty: unknown;
}
export type Basket = BasketLine[];
