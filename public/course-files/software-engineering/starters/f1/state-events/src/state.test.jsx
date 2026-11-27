// Self-check for the State & Events lesson. Run:  npm test
// The expected numbers are worked out with YOUR basket.js, so these tests
// check that the page and the rules agree. They do not give you answers.
import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, test } from 'vitest';
import App from './App.jsx';
import { NKWEN } from './data/nkwen.js';
import * as basketRules from './lib/basket.js';
import { formatFcfa } from './lib/format.js';

const products = NKWEN.products;
const card = (name) => screen.getByRole('heading', { level: 3, name }).closest('article');
const addButton = (name) => within(card(name)).getByRole('button');
const summary = () => within(screen.getByRole('banner')).getByText(/Basket:/);

describe('TODO 1: your Chapter 4 basket.js', () => {
  test('is copied in and works as an ES module', () => {
    const rice = products.find((p) => p.id === 6);
    expect(() => basketRules.addItem([], rice, 'NKW'), 'Copy your basket.js into src/lib (README step 2)').not.toThrow();
    expect(basketRules.addItem([], rice, 'NKW')).toEqual([{ id: 6, qty: 1 }]);
  });
});

describe('TODO 2 to 8: the page', () => {
  test('each Add click puts one more in the basket, and the summary agrees', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.click(addButton('Rice 50kg'));
    await user.click(addButton('Rice 50kg'));
    const expected = [{ id: 6, qty: 2 }];
    expect(summary()).toHaveTextContent(`Basket: ${basketRules.itemCount(expected)} items`);
    expect(summary()).toHaveTextContent(formatFcfa(basketRules.basketTotal(expected, products)));
  });

  test('the status message names the product that was added', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.click(addButton('Soap (bar)'));
    expect(screen.getByRole('status')).toHaveTextContent('Soap (bar)');
  });

  test('Add stops at the limit for this branch', async () => {
    const user = userEvent.setup();
    render(<App />);
    const maggi = products.find((p) => p.id === 7);
    const limit = basketRules.maxAllowed(maggi, 'NKW');
    for (let i = 0; i < limit; i += 1) await user.click(addButton(maggi.name));
    expect(addButton(maggi.name)).toBeDisabled();
    expect(addButton(maggi.name)).toHaveTextContent('Limit reached');
    expect(summary()).toHaveTextContent(`Basket: ${limit} items`);
  });

  test('a product that is out of stock here cannot be added', () => {
    render(<App />);
    const none = products.find((p) => (p.stock.NKW ?? 0) === 0);
    expect(addButton(none.name)).toBeDisabled();
    expect(addButton(none.name)).toHaveTextContent('Out of stock');
  });

  test('the Add button tells a screen reader which product it adds', () => {
    render(<App />);
    expect(addButton('Rice 50kg')).toHaveAccessibleName(/Rice 50kg/);
  });

  test('"Empty the basket" empties it', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.click(addButton('Rice 50kg'));
    await user.click(screen.getByRole('button', { name: 'Empty the basket' }));
    expect(summary()).toHaveTextContent('Basket: 0 items');
  });

  test('changing branch fits the basket to the new stock and says what changed', async () => {
    const user = userEvent.setup();
    render(<App />);
    // A product with stock at Nkwen but none at Commercial Avenue.
    const gone = products.find((p) => p.stock.NKW >= 2 && (p.stock.CAV ?? 0) === 0);
    await user.click(addButton(gone.name));
    await user.click(addButton(gone.name));
    await user.selectOptions(screen.getByLabelText('Collect at'), 'CAV');
    expect(summary()).toHaveTextContent('Basket: 0 items');
    expect(screen.getByRole('status')).toHaveTextContent(gone.name);
    expect(screen.getByRole('status')).toHaveTextContent('Commercial Avenue');
  });
});
