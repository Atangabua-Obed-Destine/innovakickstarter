// Self-check for TypeScript Essentials. Run BOTH:
//   npm test            (does the code do the right thing?)
//   npm run typecheck   (are the types right? Vitest does not check types)
import { describe, expect, test } from 'vitest';
import { NKWEN } from './data/nkwen.ts';
import * as basket from './lib/basket.ts';
import { formatFcfa, stockStatus, stockText } from './lib/format.ts';
import { isBranchCode, parseProduct } from './lib/parse.ts';
import { alertLevel, nkwenReport, restockReport } from './lib/report.ts';
import * as storage from './lib/storage.ts';
import type { Product } from './types.ts';

const products = NKWEN.products as Product[];
const rice = products.find((p) => p.id === 6)!;

describe('TODO 2: format.ts still works', () => {
  test('the Chapter 4 rules', () => {
    expect(formatFcfa(1500)).toBe('1,500 FCFA');
    expect([0, 4, 9].map(stockStatus)).toEqual(['out', 'low', 'in']);
    expect(stockText(2)).toBe('Only 2 left');
  });
});

describe('TODO 3: your typed basket.ts and storage.ts behave as in Chapter 4', () => {
  test('basket rules', () => {
    let b = basket.addItem([], rice, 'NKW');
    b = basket.addItem(b, rice, 'NKW');
    expect(b).toEqual([{ id: 6, qty: 2 }]);
    expect(basket.itemCount(b)).toBe(2);
    expect(basket.basketTotal(b, products)).toBe(2 * rice.price_fcfa);
    expect(basket.maxAllowed(rice, 'LMB')).toBe(0);
    expect(basket.revalidate([{ id: 6, qty: 9 }], products, 'ML4').basket[0].qty).toBe(Math.min(9, rice.stock.ML4!));
  });

  test('storage helpers', () => {
    expect(storage.saveBasket(localStorage, [{ id: 6, qty: 2 }])).toBe(true);
    expect(storage.loadBasket(localStorage)).toEqual([{ id: 6, qty: 2 }]);
    localStorage.setItem(storage.BASKET_KEY, '{broken');
    expect(storage.loadBasket(localStorage)).toEqual([]);
    storage.saveBranch(localStorage, 'LMB');
    expect(storage.loadBranch(localStorage, ['NKW', 'ML4', 'CAV', 'BAF'], 'NKW')).toBe('NKW');
  });
});

describe('TODO 4: checking data you do not control', () => {
  test('isBranchCode knows the five real codes', () => {
    expect(['NKW', 'ML4', 'CAV', 'BAF', 'LMB'].every(isBranchCode)).toBe(true);
    expect(['NKw', 'BUEA', ''].some(isBranchCode)).toBe(false);
  });

  test('every product from the API passes, unchanged', () => {
    for (const p of products) expect(parseProduct(JSON.parse(JSON.stringify(p))), p.name).toEqual(p);
  });

  test('bad data is refused', () => {
    expect(parseProduct({ ...rice, price_fcfa: '36,005 FCFA' })).toBeNull();
    expect(parseProduct({ ...rice, price_fcfa: -500 })).toBeNull();
    expect(parseProduct({ ...rice, name: '' })).toBeNull();
    expect(parseProduct({ ...rice, stock: { NKW: 4, XYZ: 2 } })).toBeNull();
    expect(parseProduct({ ...rice, stock: { NKW: 2.5 } })).toBeNull();
    const { stock, ...noStock } = rice;
    expect(stock).toBeDefined();
    expect(parseProduct(noStock)).toBeNull();
    for (const odd of [null, 'rice', 42, []]) expect(parseProduct(odd)).toBeNull();
  });
});

describe('TODO 5: the restock report tells the truth', () => {
  test('alertLevel follows the reorder_level rule at every branch', () => {
    for (const p of products) {
      for (const branch of ['NKW', 'ML4', 'CAV', 'BAF'] as const) {
        const stock = p.stock[branch] ?? 0;
        const expected = stock <= p.reorder_level ? 'RESTOCK' : stock <= 2 * p.reorder_level ? 'LOW' : 'OK';
        expect(alertLevel(p, branch), `${p.name} at ${branch}`).toBe(expected);
      }
    }
  });

  test('report lines have real names and stock values with commas', () => {
    const lines = restockReport(products, 'CAV');
    expect(lines.length).toBeGreaterThan(0);
    for (const line of lines) {
      const p = products.find((x) => x.name === line.name);
      expect(p, `"${line.name}" is not a product name`).toBeDefined();
      expect(line.value).toBe(formatFcfa(line.stock * p!.price_fcfa));
    }
  });

  test("Mrs. Enow Patience's report uses the Nkwen stock", () => {
    expect(nkwenReport(products)).toEqual(restockReport(products, 'NKW'));
  });
});
