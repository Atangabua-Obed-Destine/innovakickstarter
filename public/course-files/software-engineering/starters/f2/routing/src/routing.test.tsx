// Self-check for Routing. The tests use MemoryRouter: a router that keeps the
// address in memory, so a test can start on any page. Run:  npm test
import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter, useLocation } from 'react-router';
import { expect, test } from 'vitest';
import App from './App.tsx';
import { NKWEN } from './data/nkwen.ts';

function Address() {
  const location = useLocation();
  return <output aria-label="Address">{location.pathname + location.search}</output>;
}

function openAt(address: string) {
  const user = userEvent.setup();
  render(
    <MemoryRouter initialEntries={[address]}>
      <App />
      <Address />
    </MemoryRouter>,
  );
  return user;
}
const address = () => screen.getByLabelText('Address').textContent;
const productLinks = () => within(screen.getByRole('list', { name: 'Products' })).getAllByRole('link');

test('"/" is the shop, with a link to every product and "Shop" marked as current', () => {
  openAt('/');
  expect(screen.getByRole('heading', { level: 1, name: 'Shop' })).toBeInTheDocument();
  expect(productLinks()).toHaveLength(NKWEN.products.length);
  const nav = screen.getByRole('navigation', { name: 'Main' });
  expect(within(nav).getByRole('link', { name: 'Shop' })).toHaveAttribute('aria-current', 'page');
  expect(within(nav).getByRole('link', { name: 'Branches' })).not.toHaveAttribute('aria-current');
});

test('clicking a product opens its own page at /products/<id>', async () => {
  const user = openAt('/');
  await user.click(screen.getByRole('link', { name: 'Rice 50kg' }));
  expect(address()).toBe('/products/6');
  expect(screen.getByRole('heading', { level: 1, name: 'Rice 50kg' })).toBeInTheDocument();
  const open = NKWEN.branches.filter((b) => b.status === 'open').length;
  expect(screen.getAllByRole('row')).toHaveLength(open + 1); // + the header row
});

test('a product id that does not exist gets a kind "not found"', () => {
  openAt('/products/999');
  expect(screen.getByRole('heading', { level: 1, name: 'Product not found' })).toBeInTheDocument();
  expect(screen.getByRole('link', { name: 'Back to the shop' })).toHaveAttribute('href', '/');
});

test('any other address shows "Page not found", inside the layout', () => {
  openAt('/pizza');
  expect(screen.getByRole('heading', { level: 1, name: 'Page not found' })).toBeInTheDocument();
  expect(screen.getByRole('navigation', { name: 'Main' })).toBeInTheDocument();
});

test('the Branches link opens the branches page', async () => {
  const user = openAt('/');
  await user.click(screen.getByRole('link', { name: 'Branches' }));
  expect(address()).toBe('/branches');
  expect(screen.getByRole('heading', { level: 1, name: 'Our branches' })).toBeInTheDocument();
});

test('the category lives in the address: /?category=Oils shows only oils', () => {
  openAt('/?category=Oils');
  expect(screen.getByLabelText('Category')).toHaveValue('Oils');
  expect(productLinks()).toHaveLength(NKWEN.products.filter((p) => p.category === 'Oils').length);
});

test('choosing a category changes the address, and "All" clears it', async () => {
  const user = openAt('/');
  await user.selectOptions(screen.getByLabelText('Category'), 'Dairy');
  expect(address()).toBe('/?category=Dairy');
  await user.selectOptions(screen.getByLabelText('Category'), 'All');
  expect(address()).toBe('/');
});

test('"More <category>" on a product page goes back to the filtered shop', async () => {
  const user = openAt('/products/15');
  await user.click(screen.getByRole('link', { name: 'More Oils' }));
  expect(address()).toBe('/?category=Oils');
});
