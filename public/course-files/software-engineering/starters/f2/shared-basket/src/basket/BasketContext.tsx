import { createContext, useContext, useEffect, useReducer, type ReactNode } from 'react';
import { addItem, maxAllowed, quantityOf, removeItem, revalidate, setQuantity } from '../lib/basket.ts';
import { loadBasket, loadBranch, saveBasket, saveBranch } from '../lib/storage.ts';
import type { Basket, OpenBranchCode, Product } from '../types.ts';

export interface BasketState {
  branch: OpenBranchCode;
  lines: Basket;
  message: string; // what the status line tells the customer
}

// Every way the basket can change. "type" says which one; the other fields differ.
export type BasketAction =
  | { type: 'add'; product: Product }
  | { type: 'setQty'; product: Product; qty: number }
  | { type: 'remove'; product: Product }
  | { type: 'clear' }
  | { type: 'changeBranch'; branch: OpenBranchCode; branchName: string; products: Product[] };

interface BasketContextValue {
  state: BasketState;
  dispatch: (action: BasketAction) => void;
}

const OPEN_CODES: readonly OpenBranchCode[] = ['NKW', 'ML4', 'CAV', 'BAF'];

// TODO 2: the reducer: (old state, action) -> NEW state. Pure: no localStorage,
// no fetch, never change the old state. Use your basket.ts rules and a switch:
//   'add'          addItem; if the basket did not change, the message gives the limit
//                  ("You can order at most 3 of Maggi Cubes (pack) at this branch."),
//                  otherwise "Maggi Cubes (pack) added."
//   'setQty'       setQuantity;   'remove'  removeItem, "X removed."
//   'clear'        empty lines, "Your basket is empty."
//   'changeBranch' revalidate with action.products; new branch; a message that starts
//                  "Now collecting at <branchName>." and names every product that changed
export function basketReducer(state: BasketState, action: BasketAction): BasketState {
  console.log('TODO 2', action.type);
  return state;
}

// TODO 3: the starting state, read from localStorage ONCE:
//   branch from loadBranch(localStorage, OPEN_CODES, 'NKW'), lines from loadBasket(localStorage), message ''.
export function createInitialState(): BasketState {
  return { branch: 'NKW', lines: [], message: '' };
}

// TODO 4: create the context:  const BasketContext = createContext<BasketContextValue | null>(null);
// The provider keeps the state with useReducer(basketReducer, undefined, createInitialState)
// and SAVES it with an effect whenever lines or branch change
// (saveBasket and saveBranch: localStorage is a system outside React).
// Return <BasketContext value={{ state, dispatch }}>{children}</BasketContext>
export function BasketProvider({ children }: { children: ReactNode }) {
  return <>{children}</>;
}

// TODO 5: the hook every component uses. Read the context with useContext.
// If it is null, the component is outside the provider: throw a helpful Error
// that says "useBasket must be used inside <BasketProvider>".
export function useBasket(): BasketContextValue {
  return { state: createInitialState(), dispatch: (action) => console.log('TODO 5', action.type) };
}
