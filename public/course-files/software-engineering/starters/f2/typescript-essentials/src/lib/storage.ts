import type { Basket } from '../types.ts';

// TODO 3: REPLACE the code below with your own Chapter 4 storage.js,
// then add types until  npm run typecheck  is happy (README step 4).
// Keep these exact function names and parameter orders: the tests use them.
function notCopiedYet(): never {
  throw new Error('src/lib/storage.ts is still the placeholder (see the README: Bring your typed files).');
}

export const BASKET_KEY = 'nkwen-basket';
export const BRANCH_KEY = 'nkwen-branch';

export function saveBasket(_storage: Storage, _basket: Basket): boolean {
  return notCopiedYet();
}

// Always returns an array of { id, qty } lines. Missing or broken data -> [].
export function loadBasket(_storage: Storage): Basket {
  return notCopiedYet();
}

export function clearBasket(_storage: Storage): void {
  return notCopiedYet();
}

export function saveBranch(_storage: Storage, _code: string): boolean {
  return notCopiedYet();
}

// Returns the saved code if it is one of allowedCodes, otherwise the fallback.
export function loadBranch<T extends string>(_storage: Storage, _allowedCodes: readonly T[], _fallback: T): T {
  return notCopiedYet();
}
