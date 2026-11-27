// Self-check for Effects & Data Fetching, with a pretend API (no internet).
// Run:  npm test   and   npm run typecheck
import { render, screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
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

const inStock = (code: 'NKW' | 'ML4' | 'CAV' | 'BAF') => NKWEN.products.filter((p) => (p.stock[code] ?? 0) > 0).length;
const items = () => within(screen.getByRole('list', { name: 'Products' })).getAllByRole('listitem');
const productCalls = () => api.calls.filter((c) => c.path.startsWith('/products'));

test('shows "Loading products..." and then the products in stock at Nkwen', async () => {
  render(<App />);
  await screen.findByText('Loading products...');
  await screen.findByRole('list', { name: 'Products' });
  expect(items()).toHaveLength(inStock('NKW'));
  expect(productCalls()[0].path).toContain('branch=NKW');
});

test('choosing another branch loads that branch', async () => {
  const user = userEvent.setup();
  render(<App />);
  await screen.findByRole('list', { name: 'Products' });
  await user.selectOptions(screen.getByLabelText('Branch'), 'CAV');
  await waitFor(() => expect(items()).toHaveLength(inStock('CAV')));
  expect(productCalls().at(-1)!.path).toContain('branch=CAV');
});

test('Limbe says it is not open yet', async () => {
  const user = userEvent.setup();
  render(<App />);
  await screen.findByRole('list', { name: 'Products' });
  await user.selectOptions(screen.getByLabelText('Branch'), 'LMB');
  expect(await screen.findByText(/not open yet/)).toBeInTheDocument();
});

test('a server error shows an alert, and Try again loads the products', async () => {
  let failing = true;
  api.failWhen = (path) => failing && path.startsWith('/products');
  const user = userEvent.setup();
  render(<App />);
  const alert = await screen.findByRole('alert');
  failing = false;
  await user.click(within(alert).getByRole('button', { name: 'Try again' }));
  await screen.findByRole('list', { name: 'Products' });
  expect(screen.queryByRole('alert')).toBeNull();
});

test('a slow old answer never replaces a newer one (the cleanup sets ignore)', async () => {
  api.delayFor = (path) => (path.includes('branch=NKW') ? 400 : 5);
  const user = userEvent.setup();
  render(<App />);
  await screen.findByText('Loading products...');
  await user.selectOptions(screen.getByLabelText('Branch'), 'BAF');
  await screen.findByRole('list', { name: 'Products' });
  await new Promise((resolve) => setTimeout(resolve, 600)); // the slow Nkwen answer arrives now
  expect(screen.getByLabelText('Branch')).toHaveValue('BAF');
  expect(items()).toHaveLength(inStock('BAF'));
  expect(screen.getByRole('list', { name: 'Products' })).not.toHaveTextContent('Beans (Red)'); // none at Bafoussam
});

test('the effect does not loop: one request per branch', async () => {
  render(<App />);
  await screen.findByRole('list', { name: 'Products' });
  await new Promise((resolve) => setTimeout(resolve, 300));
  expect(productCalls()).toHaveLength(1);
});
