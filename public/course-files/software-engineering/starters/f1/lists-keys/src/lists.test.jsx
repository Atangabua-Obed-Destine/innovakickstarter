// Self-check for the Lists, Keys & Conditional Rendering lesson. Run:  npm test
import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, expect, test, vi } from 'vitest';
import App from './App.jsx';
import { NKWEN } from './data/nkwen.js';
import { filterProducts } from './lib/catalogue.js';

afterEach(() => {
  vi.restoreAllMocks();
});

const productList = () => screen.queryByRole('list', { name: 'Products' });

describe('TODO 1: your Chapter 4 catalogue.js', () => {
  test('is copied in and works as an ES module', () => {
    expect(() => filterProducts(NKWEN.products, {}), 'Copy your catalogue.js into src/lib').not.toThrow();
  });
});

describe('TODO 2: ProductList', () => {
  test('draws one list item per product', () => {
    render(<App />);
    const list = productList();
    expect(list, 'Return <ul aria-label="Products">').not.toBeNull();
    const items = within(list).getAllByRole('listitem');
    expect(items).toHaveLength(NKWEN.products.length);
    items.forEach((item) => expect(within(item).getByRole('article')).toBeInTheDocument());
  });

  test('gives every item a key (React prints no key warning)', () => {
    const warn = vi.spyOn(console, 'error').mockImplementation(() => {});
    render(<App />);
    const keyWarnings = warn.mock.calls.filter((call) => String(call[0]).includes('key'));
    expect(keyWarnings, 'Each <li> from map() needs key={p.id}').toEqual([]);
  });

  test('a search that matches nothing shows a helpful message instead of a list', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByLabelText('Search'), 'zzz');
    expect(productList()).toBeNull();
    expect(screen.getByRole('status')).toHaveTextContent('"zzz"');
  });

  test('search and category work together', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.selectOptions(screen.getByLabelText('Category'), 'Oils');
    const expected = filterProducts(NKWEN.products, { search: '', category: 'Oils' });
    expect(within(productList()).getAllByRole('listitem')).toHaveLength(expected.length);
  });
});

describe('TODO 3: CategoryChips', () => {
  test('one chip per category that has products; the counts add up', async () => {
    const user = userEvent.setup();
    render(<App />);
    const chips = () => within(screen.getByRole('list', { name: 'Categories' })).getAllByRole('listitem');
    expect(chips()).toHaveLength(NKWEN.categories.length);
    const total = chips().reduce((sum, chip) => sum + Number(chip.textContent.match(/\((\d+)\)/)[1]), 0);
    expect(total).toBe(NKWEN.products.length);
    await user.selectOptions(screen.getByLabelText('Category'), 'Dairy');
    expect(chips()).toHaveLength(1);
    expect(chips()[0]).toHaveTextContent('Dairy');
  });
});

describe('TODO 4: ResultCount', () => {
  test('shows how many products match', () => {
    render(<App />);
    expect(screen.getByText(`${NKWEN.products.length} products`)).toBeInTheDocument();
  });

  test('shows nothing at all (not even a 0) when nothing matches', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByLabelText('Search'), 'zzz');
    expect(screen.queryByText('0'), 'A lonely "0" is on the page').toBeNull();
  });
});

describe('Keys keep each card\'s own state', () => {
  test('a typed quantity stays with its product after sorting', async () => {
    const user = userEvent.setup();
    render(<App />);
    const box = screen.getByLabelText('Quantity for Rice 50kg');
    await user.clear(box);
    await user.type(box, '3');
    await user.click(screen.getByLabelText('Cheapest first'));
    expect(screen.getByLabelText('Quantity for Rice 50kg')).toHaveValue(3);
  });
});
