// Self-check: the user stories in README.md, tested with a pretend API.
// Expected numbers come from YOUR Chapter 4 files, so the tests check that the
// page and your rules agree. Run:  npm test
import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import App from './App.jsx';
import { NKWEN } from './data/nkwen.js';
import * as rules from './lib/basket.js';
import { filterProducts } from './lib/catalogue.js';
import { formatFcfa, stockText } from './lib/format.js';
import { BASKET_KEY } from './lib/storage.js';
import { installFakeApi } from './test/fakeApi.js';

let api;
beforeEach(() => {
  api = installFakeApi();
});
afterEach(() => {
  vi.unstubAllGlobals();
});

const products = NKWEN.products;
const byId = (id) => products.find((p) => p.id === id);
async function openShop() {
  const user = userEvent.setup();
  const view = render(<App />);
  await screen.findByRole('list', { name: 'Products' });
  return { user, ...view };
}
const cards = () => within(screen.getByRole('list', { name: 'Products' })).getAllByRole('article');
const card = (name) => screen.getByRole('heading', { level: 3, name }).closest('article');
const addButton = (name) => within(card(name)).getByRole('button');
const basket = () => screen.getByRole('complementary', { name: 'Your basket' });
const headerCount = () => within(screen.getByRole('banner')).getByText(/Basket:/);

describe('Your Chapter 4 files', () => {
  test('basket.js, catalogue.js and storage.js are copied in as ES modules', () => {
    expect(() => rules.itemCount([]), 'Copy basket.js (README)').not.toThrow();
    expect(() => filterProducts(products, {}), 'Copy catalogue.js (README)').not.toThrow();
    expect(BASKET_KEY).toBeTruthy();
  });
});

describe('Stories 1-2: live data, loading and errors', () => {
  test('1. shows a loading message, then one card per product from the API', async () => {
    render(<App />);
    expect(screen.getByText('Loading products...')).toBeInTheDocument();
    await screen.findByRole('list', { name: 'Products' });
    expect(cards()).toHaveLength(products.length);
    expect(api.calls.some((c) => c.includes('/products'))).toBe(true);
  });

  test('2. a server error shows an alert with Try again, which works', async () => {
    api.failing = true;
    const user = userEvent.setup();
    render(<App />);
    const alert = await screen.findByRole('alert');
    expect(screen.queryByRole('list', { name: 'Products' })).toBeNull();
    api.failing = false;
    await user.click(within(alert).getByRole('button', { name: 'Try again' }));
    await screen.findByRole('list', { name: 'Products' });
    expect(screen.queryByRole('alert')).toBeNull();
  });
});

describe('Stories 3-5: search, category, empty message', () => {
  test('3. search filters as I type, ignoring case and spaces', async () => {
    const { user } = await openShop();
    await user.type(screen.getByLabelText('Search'), '  RICE ');
    const expected = filterProducts(products, { search: '  RICE ', category: 'All' });
    expect(cards()).toHaveLength(expected.length);
    await user.clear(screen.getByLabelText('Search'));
    expect(cards()).toHaveLength(products.length);
  });

  test('4. category and search work together', async () => {
    const { user } = await openShop();
    await user.selectOptions(screen.getByLabelText('Category'), 'Oils');
    await user.type(screen.getByLabelText('Search'), 'palm');
    expect(cards()).toHaveLength(filterProducts(products, { search: 'palm', category: 'Oils' }).length);
    cards().forEach((c) => expect(c).toHaveTextContent('Oils'));
  });

  test('5. no match gives a helpful message', async () => {
    const { user } = await openShop();
    await user.type(screen.getByLabelText('Search'), 'zzz');
    expect(screen.queryByRole('list', { name: 'Products' })).toBeNull();
    expect(screen.getByText(/zzz/)).toBeInTheDocument();
  });
});

describe('Stories 6-7: cards and branches', () => {
  test('6. each card shows the price, the unit and the stock badge for my branch', async () => {
    await openShop();
    const rice = byId(6);
    expect(within(card(rice.name)).getByText(`${formatFcfa(rice.price_fcfa)} / ${rice.unit}`)).toBeInTheDocument();
    expect(within(card(rice.name)).getByText(stockText(rice.stock.NKW))).toBeInTheDocument();
    const none = products.find((p) => (p.stock.NKW ?? 0) === 0);
    expect(addButton(none.name)).toBeDisabled();
  });

  test('7. "Collect at" offers only the open branches', async () => {
    await openShop();
    const names = within(screen.getByLabelText('Collect at')).getAllByRole('option').map((o) => o.value);
    expect(names).toEqual(NKWEN.branches.filter((b) => b.status === 'open').map((b) => b.code));
  });
});

describe('Stories 8-9: the basket', () => {
  test('8a. Add stops at the limit, and the message says so', async () => {
    const { user } = await openShop();
    const maggi = byId(7);
    const limit = rules.maxAllowed(maggi, 'NKW');
    for (let i = 0; i < limit; i += 1) await user.click(addButton(maggi.name));
    expect(addButton(maggi.name)).toBeDisabled();
    expect(screen.getByRole('status')).toHaveTextContent(maggi.name);
    expect(headerCount()).toHaveTextContent(`Basket: ${limit} items`);
  });

  test('8b. one more, one less and Remove change only that line', async () => {
    const { user } = await openShop();
    const rice = byId(6);
    const sugar = byId(9);
    await user.click(addButton(rice.name));
    await user.click(addButton(sugar.name));
    await user.click(within(basket()).getByRole('button', { name: `One more ${rice.name}` }));
    expect(headerCount()).toHaveTextContent('Basket: 3 items');
    await user.click(within(basket()).getByRole('button', { name: `One less ${rice.name}` }));
    expect(headerCount()).toHaveTextContent('Basket: 2 items');
    await user.click(within(basket()).getByRole('button', { name: `Remove ${rice.name}` }));
    expect(within(basket()).queryByText(rice.name)).toBeNull();
    expect(within(basket()).getByText(sugar.name)).toBeInTheDocument();
  });

  test('9. line totals and the basket total agree with your rules', async () => {
    const { user } = await openShop();
    await user.click(addButton('Rice 50kg'));
    await user.click(addButton('Rice 50kg'));
    await user.click(addButton('Soap (bar)'));
    const lines = [{ id: 6, qty: 2 }, { id: 13, qty: 1 }];
    expect(within(basket()).getByText(formatFcfa(basketTotalOf(lines)))).toBeInTheDocument();
    expect(within(basket()).getByText(formatFcfa(2 * byId(6).price_fcfa))).toBeInTheDocument();
    expect(headerCount()).toHaveTextContent(`Basket: ${rules.itemCount(lines)} items`);
  });
});

function basketTotalOf(lines) {
  return rules.basketTotal(lines, products);
}

describe('Stories 10-11: saved basket and branch changes', () => {
  test('10a. the basket and the branch survive a refresh', async () => {
    const { user, unmount } = await openShop();
    await user.selectOptions(screen.getByLabelText('Collect at'), 'BAF');
    await user.click(addButton('Rice 50kg'));
    unmount();
    await openShop();
    expect(screen.getByLabelText('Collect at')).toHaveValue('BAF');
    expect(within(basket()).getByText('Rice 50kg')).toBeInTheDocument();
  });

  test('10b. broken saved data does not crash the page', async () => {
    localStorage.setItem(BASKET_KEY, '{broken');
    await openShop();
    expect(headerCount()).toHaveTextContent('Basket: 0 items');
  });

  test('11. changing branch fits the basket and says what changed', async () => {
    const { user } = await openShop();
    const gone = products.find((p) => p.stock.NKW >= 1 && (p.stock.CAV ?? 0) === 0);
    await user.click(addButton(gone.name));
    await user.selectOptions(screen.getByLabelText('Collect at'), 'CAV');
    expect(within(basket()).queryByText(gone.name)).toBeNull();
    expect(screen.getByRole('status')).toHaveTextContent(gone.name);
    expect(screen.getByRole('status')).toHaveTextContent('Commercial Avenue');
  });
});

describe('Story 12: React code, not DOM code', () => {
  test('components never touch the page directly (no document. or innerHTML)', () => {
    const sources = import.meta.glob('./components/*.jsx', { query: '?raw', import: 'default', eager: true });
    expect(Object.keys(sources).length).toBeGreaterThanOrEqual(6);
    for (const [file, code] of Object.entries(sources)) {
      expect(code, file).not.toMatch(/document\.|innerHTML/);
    }
  });
});
