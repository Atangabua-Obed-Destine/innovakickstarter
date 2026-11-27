import type { Basket, BranchCode, Product } from '../types.ts';

// TODO 3: REPLACE the code below with your own Chapter 4 basket.js,
// then add types until  npm run typecheck  is happy (README step 4).
// Keep these exact function names and parameter orders: the tests use them.
function notCopiedYet(): never {
  throw new Error('src/lib/basket.ts is still the placeholder (see the README: Bring your typed files).');
}

export const MAX_PER_PRODUCT = 10;

export interface BasketLineView {
  id: number;
  name: string;
  unit: string;
  qty: number;
  price_fcfa: number;
  line_total: number;
}

export interface BasketChange {
  id: number;
  name: string;
  from: number;
  to: number;
}

export function maxAllowed(_product: Product, _branch: BranchCode): number {
  return notCopiedYet();
}

export function quantityOf(_basket: Basket, _id: number): number {
  return notCopiedYet();
}

export function removeItem(_basket: Basket, _id: number): Basket {
  return notCopiedYet();
}

export function setQuantity(_basket: Basket, _product: Product, _branch: BranchCode, _qty: number | string): Basket {
  return notCopiedYet();
}

export function addItem(_basket: Basket, _product: Product, _branch: BranchCode): Basket {
  return notCopiedYet();
}

export function itemCount(_basket: Basket): number {
  return notCopiedYet();
}

export function basketLines(_basket: Basket, _products: Product[]): BasketLineView[] {
  return notCopiedYet();
}

export function basketTotal(_basket: Basket, _products: Product[]): number {
  return notCopiedYet();
}

export function revalidate(_basket: Basket, _products: Product[], _branch: BranchCode): { basket: Basket; changes: BasketChange[] } {
  return notCopiedYet();
}
