// Self-check: the user stories in README.md, with a pretend API (no internet).
// Run:  npm test   and   npm run typecheck   (both must pass)
import { render, screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter, useLocation } from 'react-router';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import App from './App.tsx';
import { NKWEN } from './data/nkwen.ts';
import { basketTotal } from './lib/basket.ts';
import { formatFcfa } from './lib/format.ts';
import { installFakeApi, type FakeApi } from './test/fakeApi.ts';

let api: FakeApi;
beforeEach(() => {
  api = installFakeApi();
});
afterEach(() => {
  vi.unstubAllGlobals();
});

function Address() {
  const location = useLocation();
  return <p aria-label="Address">{location.pathname}</p>;
}
function openAt(address: string) {
  const user = userEvent.setup();
  const view = render(
    <MemoryRouter initialEntries={[address]}>
      <App />
      <Address />
    </MemoryRouter>,
  );
  return { user, ...view };
}
const address = () => screen.getByLabelText('Address').textContent;
const byId = (id: number) => NKWEN.products.find((p) => p.id === id)!;
const navBasket = () => within(screen.getByRole('navigation', { name: 'Main' })).getByRole('link', { name: /Basket/ });
const posts = () => api.calls.filter((c) => c.method === 'POST');

async function addFromShop(user: ReturnType<typeof userEvent.setup>, name: string, times = 1) {
  for (let i = 0; i < times; i += 1) await user.click(await screen.findByRole('button', { name: `Add ${name}` }));
}
async function goToCheckout(user: ReturnType<typeof userEvent.setup>) {
  await user.click(navBasket());
  await user.click(await screen.findByRole('link', { name: 'Go to checkout' }));
  await screen.findByRole('form', { name: 'Checkout' });
}

describe('Stories 2-3: pages, routes and live data', () => {
  test('the shop loads the products from the API', async () => {
    openAt('/');
    expect(screen.getByText('Loading products...')).toBeInTheDocument();
    const list = await screen.findByRole('list', { name: 'Products' });
    expect(within(list).getAllByRole('article')).toHaveLength(NKWEN.products.length);
  });

  test('a product name opens its page; a wrong address shows "Page not found"', async () => {
    const { user, unmount } = openAt('/');
    await user.click(await screen.findByRole('link', { name: 'Rice 50kg' }));
    expect(address()).toBe('/products/6');
    expect(await screen.findByRole('heading', { level: 1, name: 'Rice 50kg' })).toBeInTheDocument();
    unmount();
    openAt('/no-such-page');
    expect(screen.getByRole('heading', { level: 1, name: 'Page not found' })).toBeInTheDocument();
  });

  test('a server error shows an alert with Try again', async () => {
    let failing = true;
    api.failWhen = () => failing;
    const { user } = openAt('/');
    const alert = await screen.findByRole('alert');
    failing = false;
    await user.click(within(alert).getByRole('button', { name: 'Try again' }));
    expect(await screen.findByRole('list', { name: 'Products' })).toBeInTheDocument();
  });
});

describe('Stories 4-5: one basket on every page', () => {
  test('added on the product page, counted in the menu, listed on the basket page', async () => {
    const { user } = openAt('/products/6');
    await user.click(await screen.findByRole('button', { name: 'Add to basket' }));
    await user.click(screen.getByRole('button', { name: 'Add to basket' }));
    expect(navBasket()).toHaveTextContent('2 items');
    await user.click(navBasket());
    expect(await screen.findByRole('heading', { level: 1, name: 'Your basket' })).toBeInTheDocument();
    expect(screen.getByLabelText('Quantity of Rice 50kg')).toHaveValue('2');
    expect(screen.getByText('Total:', { exact: false })).toHaveTextContent(formatFcfa(basketTotal([{ id: 6, qty: 2 }], NKWEN.products)));
  });

  test('the basket page changes quantities and removes lines', async () => {
    const { user } = openAt('/');
    await addFromShop(user, 'Soap (bar)');
    await user.click(navBasket());
    await user.selectOptions(await screen.findByLabelText('Quantity of Soap (bar)'), '4');
    expect(navBasket()).toHaveTextContent('4 items');
    await user.click(screen.getByRole('button', { name: 'Remove Soap (bar)' }));
    expect(navBasket()).toHaveTextContent('0 items');
    expect(screen.queryByRole('link', { name: 'Go to checkout' })).toBeNull();
  });

  test('the basket survives a refresh', async () => {
    const first = openAt('/');
    await addFromShop(first.user, 'Rice 50kg');
    first.unmount();
    openAt('/basket');
    expect(await screen.findByLabelText('Quantity of Rice 50kg')).toHaveValue('1');
  });
});

describe('Stories 6-8: checkout and the 201 answer', () => {
  test('nothing is sent while the form has errors', async () => {
    const { user } = openAt('/');
    await addFromShop(user, 'Rice 50kg');
    await goToCheckout(user);
    await user.click(screen.getByRole('button', { name: 'Place order' }));
    expect(screen.getByLabelText('Your name')).toHaveAttribute('aria-invalid', 'true');
    expect(posts()).toHaveLength(0);
  });

  test('a cash order: ids and quantities only, an Idempotency-Key, then the confirmation', async () => {
    const { user } = openAt('/');
    await addFromShop(user, 'Rice 50kg', 2);
    await addFromShop(user, 'Palm Oil 5L');
    await goToCheckout(user);
    await user.type(screen.getByLabelText('Your name'), 'Mbah Clarisse');
    await user.selectOptions(screen.getByLabelText('Collect at'), 'CAV');
    await user.click(screen.getByRole('button', { name: 'Place order' }));
    expect(await screen.findByRole('heading', { level: 1, name: /Order ORD-\w+ is booked/ })).toBeInTheDocument();
    expect(address()).toBe('/done');
    const [post] = posts();
    expect(post.body).toEqual({ branch: 'CAV', customer_name: 'Mbah Clarisse', payment_method: 'cash', items: [{ product_id: 6, quantity: 2 }, { product_id: 15, quantity: 1 }] });
    expect(post.headers['idempotency-key']).toMatch(/^[0-9a-f-]{36}$/);
    const total = 2 * byId(6).price_fcfa + byId(15).price_fcfa;
    expect(screen.getByText(formatFcfa(total))).toBeInTheDocument();
    expect(navBasket()).toHaveTextContent('0 items');
  });

  test('a mobile-money order goes to the simulated payment screen', async () => {
    const { user } = openAt('/');
    await addFromShop(user, 'Sugar 1kg');
    await goToCheckout(user);
    await user.type(screen.getByLabelText('Your name'), 'Mbah Clarisse');
    await user.click(screen.getByLabelText('MTN Mobile Money'));
    await user.type(screen.getByLabelText('Mobile money number'), '670 00 00 01');
    await user.click(screen.getByRole('button', { name: 'Place order' }));
    await user.click(await screen.findByRole('button', { name: 'Send payment request to 670 00 00 01' }));
    expect(screen.getByRole('status')).toHaveTextContent(/Never type your PIN/);
    await user.click(screen.getByRole('button', { name: 'Customer approves' }));
    expect(await screen.findByText(/Payment received \(simulated\)/, undefined, { timeout: 3000 })).toBeInTheDocument();
  });

  test('/pay without an order says so', () => {
    openAt('/pay');
    expect(screen.getByRole('heading', { level: 1, name: 'No order to pay' })).toBeInTheDocument();
  });
});

describe('Stories 9-11: 422, 409 and 500', () => {
  test('422: with the browser checks skipped, the server message appears under the name', async () => {
    const { user } = openAt('/');
    await addFromShop(user, 'Sugar 1kg');
    await goToCheckout(user);
    await user.click(screen.getByLabelText(/skip the browser checks/));
    await user.type(screen.getByLabelText('Your name'), 'A');
    await user.click(screen.getByRole('button', { name: 'Place order' }));
    expect(await screen.findByText('The customer name field must be at least 2 characters.')).toBeInTheDocument();
    expect(screen.getByLabelText('Your name')).toHaveAttribute('aria-invalid', 'true');
  });

  test('409: asked for 10, 5 available; "Use 5 instead", then the order goes through', async () => {
    const { user } = openAt('/');
    await user.selectOptions(await screen.findByLabelText('Collect at'), 'ML4');
    await addFromShop(user, 'Rice 25kg', 10);
    await goToCheckout(user);
    await user.type(screen.getByLabelText('Your name'), 'Mbah Clarisse');
    await user.selectOptions(screen.getByLabelText('Collect at'), 'NKW');
    await user.click(screen.getByRole('button', { name: 'Place order' }));
    const alert = await screen.findByRole('alert');
    expect(alert).toHaveTextContent('asked for 10');
    expect(alert).toHaveTextContent(`${byId(5).stock.NKW} are available`);
    await user.click(within(alert).getByRole('button', { name: `Use ${byId(5).stock.NKW} instead` }));
    await user.click(screen.getByRole('button', { name: 'Place order' }));
    expect(await screen.findByRole('heading', { level: 1, name: /is booked/ })).toBeInTheDocument();
    expect(posts().at(-1)!.body).toMatchObject({ branch: 'NKW', items: [{ product_id: 5, quantity: byId(5).stock.NKW }] });
  });

  test('500: a calm message, the basket is kept, and the button works again', async () => {
    const { user } = openAt('/');
    await addFromShop(user, 'Sugar 1kg');
    await goToCheckout(user);
    api.failWhen = (path) => path.startsWith('/orders');
    await user.type(screen.getByLabelText('Your name'), 'Mbah Clarisse');
    await user.click(screen.getByRole('button', { name: 'Place order' }));
    expect(await screen.findByRole('alert')).toHaveTextContent(/try again/i);
    expect(navBasket()).toHaveTextContent('1 items');
    await waitFor(() => expect(screen.getByRole('button', { name: 'Place order' })).toBeEnabled());
    api.failWhen = () => false;
    await user.click(screen.getByRole('button', { name: 'Place order' }));
    expect(await screen.findByRole('heading', { level: 1, name: /is booked/ })).toBeInTheDocument();
    const [first, second] = posts();
    expect(second.headers['idempotency-key'], 'a retry after a 500 sends the SAME key').toBe(first.headers['idempotency-key']);
  });
});
