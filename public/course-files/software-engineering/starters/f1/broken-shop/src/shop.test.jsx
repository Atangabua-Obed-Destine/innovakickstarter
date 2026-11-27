// What the shop SHOULD do. These tests are correct: do not change them.
// Run:  npm test   (all 7 fail at first, because the first bug crashes the page)
import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { expect, test } from 'vitest';
import App from './App.jsx';
import { NKWEN } from './data/nkwen.js';
import { formatFcfa } from './lib/format.js';

const rice = NKWEN.products.find((p) => p.id === 6);
const sugar = NKWEN.products.find((p) => p.id === 9);

async function openShop() {
  const user = userEvent.setup();
  render(<App />);
  await screen.findAllByRole('article');
  return user;
}
const basket = () => screen.getByRole('complementary', { name: 'Your basket' });
const header = () => within(screen.getByRole('banner')).getByText(/basket/i);
const add = (user, product) => user.click(screen.getByRole('button', { name: `Add ${product.name}` }));

test('1. every product appears once the data has loaded', async () => {
  await openShop();
  expect(screen.getAllByRole('article')).toHaveLength(NKWEN.products.length);
});

test('2. an empty basket says so in the header, with nothing else', async () => {
  await openShop();
  expect(header().textContent).toBe('Your basket is empty');
});

test('3. adding a product puts it in the basket straight away', async () => {
  const user = await openShop();
  await add(user, rice);
  expect(within(basket()).getByText(rice.name, { selector: '.line-name' })).toBeInTheDocument();
  expect(header().textContent).toBe('Basket: 1 item');
});

test('4. adding the same product twice gives one line with quantity 2', async () => {
  const user = await openShop();
  await add(user, rice);
  await add(user, rice);
  expect(within(basket()).getAllByRole('listitem')).toHaveLength(1);
  expect(screen.getByLabelText(`Quantity of ${rice.name}`)).toHaveValue('2');
});

test('5. a line stays until I press Remove, and Remove takes only that line', async () => {
  const user = await openShop();
  await add(user, rice);
  await add(user, sugar);
  expect(within(basket()).getAllByRole('listitem')).toHaveLength(2);
  await user.click(screen.getByRole('button', { name: `Remove ${rice.name}` }));
  expect(within(basket()).queryByText(rice.name, { selector: '.line-name' })).toBeNull();
  expect(within(basket()).getByText(sugar.name, { selector: '.line-name' })).toBeInTheDocument();
});

test('6. choosing a quantity, then adding one more, counts as numbers', async () => {
  const user = await openShop();
  await add(user, rice);
  await user.selectOptions(screen.getByLabelText(`Quantity of ${rice.name}`), '3');
  await add(user, rice);
  expect(header().textContent).toBe('Basket: 4 items');
});

test('7. the total is price x quantity', async () => {
  const user = await openShop();
  await add(user, sugar);
  await user.selectOptions(screen.getByLabelText(`Quantity of ${sugar.name}`), '5');
  await add(user, rice);
  expect(within(basket()).getByText(formatFcfa(5 * sugar.price_fcfa + rice.price_fcfa))).toBeInTheDocument();
});
