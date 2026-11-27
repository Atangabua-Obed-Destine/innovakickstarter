// Self-check for Shared State. Run:  npm test   and   npm run typecheck
import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, test, vi } from 'vitest';
import App from './App.tsx';
import { basketReducer, createInitialState, useBasket, type BasketState } from './basket/BasketContext.tsx';
import { NKWEN } from './data/nkwen.ts';
import * as rules from './lib/basket.ts';
import { BASKET_KEY, BRANCH_KEY } from './lib/storage.ts';

const byId = (id: number) => NKWEN.products.find((p) => p.id === id)!;
const start: BasketState = Object.freeze({ branch: 'NKW', lines: Object.freeze([]) as never, message: '' });

describe('TODO 1: your typed basket.ts and storage.ts', () => {
  test('are copied in', () => {
    expect(() => rules.itemCount([]), 'Copy basket.ts from TypeScript Essentials').not.toThrow();
    expect(BASKET_KEY).toBeTruthy();
  });
});

describe('TODO 2: basketReducer (a pure function)', () => {
  test('add puts one more in, and says so', () => {
    const rice = byId(6);
    const once = basketReducer(start, { type: 'add', product: rice });
    const twice = basketReducer(once, { type: 'add', product: rice });
    expect(twice.lines).toEqual([{ id: 6, qty: 2 }]);
    expect(twice.message).toContain(rice.name);
    expect(start.lines).toEqual([]); // the old state is untouched
  });

  test('add at the limit keeps the lines and explains the limit', () => {
    const maggi = byId(7);
    let state = start;
    const limit = rules.maxAllowed(maggi, 'NKW');
    for (let i = 0; i < limit; i += 1) state = basketReducer(state, { type: 'add', product: maggi });
    const after = basketReducer(state, { type: 'add', product: maggi });
    expect(after.lines).toBe(state.lines);
    expect(after.message).toContain(String(limit));
  });

  test('setQty, remove and clear', () => {
    const soap = byId(13);
    let state = basketReducer(start, { type: 'setQty', product: soap, qty: 4 });
    expect(state.lines).toEqual([{ id: 13, qty: 4 }]);
    state = basketReducer(state, { type: 'remove', product: soap });
    expect(state.lines).toEqual([]);
    state = basketReducer(basketReducer(state, { type: 'add', product: soap }), { type: 'clear' });
    expect(state.lines).toEqual([]);
  });

  test('changeBranch fits the basket to the new branch and names what changed', () => {
    const gone = NKWEN.products.find((p) => (p.stock.NKW ?? 0) > 0 && (p.stock.CAV ?? 0) === 0)!;
    const state = basketReducer(start, { type: 'add', product: gone });
    const moved = basketReducer(state, { type: 'changeBranch', branch: 'CAV', branchName: 'Commercial Avenue', products: NKWEN.products });
    expect(moved.branch).toBe('CAV');
    expect(moved.lines).toEqual([]);
    expect(moved.message).toMatch(/^Now collecting at Commercial Avenue\./);
    expect(moved.message).toContain(gone.name);
  });
});

describe('TODO 3 and 4: the provider shares and saves one basket', () => {
  test('Add in the product list updates the header AND the basket panel', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.click(screen.getByRole('button', { name: 'Add Rice 50kg' }));
    await user.click(screen.getByRole('button', { name: 'Add Rice 50kg' }));
    expect(within(screen.getByRole('banner')).getByText(/Basket:/)).toHaveTextContent('Basket: 2 items');
    expect(within(screen.getByRole('complementary', { name: 'Your basket' })).getByText('2 x Rice 50kg')).toBeInTheDocument();
    expect(screen.getByRole('status')).toHaveTextContent('Rice 50kg');
  });

  test('the basket and branch are saved, and read back on the next visit', async () => {
    const user = userEvent.setup();
    const first = render(<App />);
    await user.selectOptions(screen.getByLabelText('Collect at'), 'BAF');
    await user.click(screen.getByRole('button', { name: 'Add Soap (bar)' }));
    expect(JSON.parse(localStorage.getItem(BASKET_KEY)!)).toEqual([{ id: 13, qty: 1 }]);
    expect(localStorage.getItem(BRANCH_KEY)).toBe('BAF');
    first.unmount();
    render(<App />);
    expect(screen.getByLabelText('Collect at')).toHaveValue('BAF');
    expect(within(screen.getByRole('banner')).getByText(/Basket:/)).toHaveTextContent('Basket: 1 items');
  });

  test('broken saved data starts an empty basket', () => {
    localStorage.setItem(BASKET_KEY, '{broken');
    expect(createInitialState().lines).toEqual([]);
  });
});

describe('TODO 5: useBasket', () => {
  test('outside the provider it throws a helpful error', () => {
    vi.spyOn(console, 'error').mockImplementation(() => {});
    function Lost() {
      useBasket();
      return null;
    }
    expect(() => render(<Lost />)).toThrow(/BasketProvider/);
    vi.restoreAllMocks();
  });
});
