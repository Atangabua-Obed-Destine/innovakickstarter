// Self-check for TODO 2, 3 and 4. Run:  npm test
// The tests find things the way a screen reader user would: by role and by name.
import { render, screen, within } from '@testing-library/react';
import { describe, expect, test } from 'vitest';
import Footer from './components/Footer.jsx';
import Header from './components/Header.jsx';
import ProductCard from './components/ProductCard.jsx';
import { NKWEN } from './data/nkwen.js';
import { formatFcfa } from './lib/format.js';

describe('TODO 2: Header', () => {
  test('is a <header> with the shop name', () => {
    render(<Header />);
    const banner = screen.queryByRole('banner');
    expect(banner, 'Header should return a <header> element').not.toBeNull();
    expect(banner).toHaveTextContent('Nkwen Traders');
  });

  test('has a "Main" navigation with three links', () => {
    render(<Header />);
    const nav = screen.queryByRole('navigation', { name: 'Main' });
    expect(nav, 'Add <nav aria-label="Main"> inside the header').not.toBeNull();
    const names = within(nav).getAllByRole('link').map((link) => link.textContent);
    expect(names).toEqual(['Shop', 'Branches', 'Contact']);
  });
});

describe('TODO 3: ProductCard', () => {
  const product = NKWEN.products.find((p) => p.id === 6);

  test('is an <article> with the product name in an <h3>', () => {
    render(<ProductCard />);
    expect(screen.queryByRole('article'), 'ProductCard should return an <article>').not.toBeNull();
    expect(screen.getByRole('heading', { level: 3 })).toHaveTextContent(product.name);
  });

  test('shows the category, and the price with its unit', () => {
    const { container } = render(<ProductCard />);
    const category = container.querySelector('.category');
    expect(category, 'Add a <p className="category">').not.toBeNull();
    expect(category).toHaveTextContent(product.category);
    const price = container.querySelector('.price');
    expect(price, 'Add a <p className="price">').not.toBeNull();
    expect(price.textContent).toBe(`${formatFcfa(product.price_fcfa)} / ${product.unit}`);
  });
});

describe('TODO 4: Footer', () => {
  test('is a <footer> with this year and the company name', () => {
    render(<Footer />);
    const footer = screen.queryByRole('contentinfo');
    expect(footer, 'Footer should return a <footer> element').not.toBeNull();
    expect(footer).toHaveTextContent(String(new Date().getFullYear()));
    expect(footer).toHaveTextContent('Nkwen Traders Ltd.');
  });
});
