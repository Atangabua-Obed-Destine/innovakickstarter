// What the shop SHOULD do. These tests are correct: do not change them.
// They use a pretend API that can answer slowly on purpose. Run:  npm test
import { render, screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import App from './App.tsx';
import { NKWEN } from './data/nkwen.ts';
import { installFakeApi, type FakeApi } from './test/fakeApi.ts';

let api: FakeApi;
beforeEach(() => {
  api = installFakeApi();
});
afterEach(() => {
  vi.unstubAllGlobals();
});

function openAt(address: string) {
  const user = userEvent.setup();
  render(
    <MemoryRouter initialEntries={[address]}>
      <App />
    </MemoryRouter>,
  );
  return user;
}
const wait = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms));
const productCalls = () => api.calls.filter((c) => c.path.startsWith('/products?') || c.path === '/products');
const names = () => within(screen.getByRole('list', { name: 'Products' })).getAllByRole('link').map((a) => a.textContent);

test('1. the shop asks for the products once, not again and again', async () => {
  openAt('/');
  await screen.findByRole('list', { name: 'Products' });
  await wait(300);
  expect(productCalls().length).toBe(1);
});

test('2. typing fast on an uneven network: the list matches what is in the box', async () => {
  api.delayFor = (path) => (/search=[^&]{1,2}(&|$)/.test(path) ? 400 : 20);
  const user = openAt('/');
  await screen.findByRole('list', { name: 'Products' });
  await user.type(screen.getByLabelText('Search'), 'rice');
  await wait(800); // every slow, old answer has arrived by now
  expect(names().length).toBeGreaterThan(0);
  expect(names().every((n) => n!.toLowerCase().includes('rice'))).toBe(true);
});

test('3. "Next product" shows the next product, not the old one', async () => {
  const user = openAt('/products/6');
  expect(await screen.findByRole('heading', { level: 1, name: 'Rice 50kg' })).toBeInTheDocument();
  await user.click(screen.getByRole('link', { name: 'Next product' }));
  const next = NKWEN.products.find((p) => p.id === 7)!;
  expect(await screen.findByRole('heading', { level: 1, name: next.name })).toBeInTheDocument();
});

test('4. "Add 5" adds five', async () => {
  const user = openAt('/products/6');
  await screen.findByRole('heading', { level: 1, name: 'Rice 50kg' });
  await user.click(screen.getByRole('button', { name: 'Add 5' }));
  expect(screen.getByLabelText('Quantity')).toHaveTextContent('6');
});

test('5. a double click places ONE order, sent with an Idempotency-Key', async () => {
  api.delayFor = (path) => (path.startsWith('/orders') ? 200 : 5);
  const user = openAt('/products/6');
  await screen.findByRole('heading', { level: 1, name: 'Rice 50kg' });
  await user.dblClick(screen.getByRole('button', { name: 'Place test order' }));
  await screen.findByText(/checked:/);
  const posts = api.calls.filter((c) => c.method === 'POST');
  expect(posts).toHaveLength(1);
  expect(posts[0].headers['idempotency-key']).toMatch(/^[0-9a-f-]{36}$/);
});

test('6. the order total is price x quantity', async () => {
  const user = openAt('/products/6');
  await screen.findByRole('heading', { level: 1, name: 'Rice 50kg' });
  await user.click(screen.getByRole('button', { name: 'Add 1' }));
  await user.click(screen.getByRole('button', { name: 'Place test order' }));
  const rice = NKWEN.products.find((p) => p.id === 6)!;
  await waitFor(() => expect(screen.getByText(/checked:/)).toHaveTextContent((2 * rice.price_fcfa).toLocaleString('en-US')));
});
