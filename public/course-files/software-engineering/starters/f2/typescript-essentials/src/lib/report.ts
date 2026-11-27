// @ts-nocheck
// TODO 5: a colleague wrote the branch manager's restock report in plain
// JavaScript. It runs, but the numbers are wrong and nobody saw an error.
// Delete the first line (@ts-nocheck), run  npm run typecheck  and read the
// errors in THIS file: TypeScript finds every bug. Fix them, and write each
// one in TYPEBUGS.md.
import type { OpenBranchCode, Product } from '../types.ts';
import { formatFcfa } from './format.ts';

export type AlertLevel = 'OK' | 'LOW' | 'RESTOCK';

export interface ReportLine {
  name: string;
  level: AlertLevel;
  stock: number;
  value: string; // the stock's value in FCFA, e.g. "64,100 FCFA"
}

// The manager's restock alert (the Chapter 1 "Restock Alerts" rule, with reorder_level):
// stock at or below reorder_level -> RESTOCK; at or below 2 x reorder_level -> LOW; otherwise OK.
// (This is NOT the customer's shop badge.)
export function alertLevel(product: Product, branch: OpenBranchCode): AlertLevel {
  const stock = product.stock[branch] ?? 0;
  if (stock <= product.reorderLevel) return 'RESTOCK';
  if (stock <= 2 * product.reorder_level) return 'LOW';
  return 'OK';
}

// One line for every product that is not OK, RESTOCK lines first.
export function restockReport(products: Product[], branch: OpenBranchCode): ReportLine[] {
  return products
    .map((p) => {
      const stock = p.stock[branch] ?? 0;
      return { name: p.Name, level: alertLevel(p, branch), stock, value: formatFcfa(String(stock * p.price_fcfa)) };
    })
    .filter((line) => line.level !== 'OK')
    .sort((a, b) => (a.level === b.level ? 0 : a.level === 'RESTOCK' ? -1 : 1));
}

// The report Mrs. Enow Patience reads every Monday.
export function nkwenReport(products: Product[]): ReportLine[] {
  return restockReport(products, 'NKw');
}
