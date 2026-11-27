// Self-check for the Props lesson. Run:  npm test
import { render, screen } from '@testing-library/react';
import { describe, expect, test } from 'vitest';
import App from './App.jsx';
import Panel from './components/Panel.jsx';
import ProductCard from './components/ProductCard.jsx';
import StockBadge from './components/StockBadge.jsx';
import { NKWEN } from './data/nkwen.js';
import { formatFcfa, stockStatus, stockText } from './lib/format.js';

const byId = (id) => NKWEN.products.find((p) => p.id === id);

describe('TODO 1: StockBadge', () => {
  test.each([0, 3, 42])('quantity %i shows the right words and class', (quantity) => {
    const { container } = render(<StockBadge quantity={quantity} />);
    const badge = container.querySelector('p');
    expect(badge.textContent).toBe(stockText(quantity));
    expect(badge, 'Use className={`stock stock--${...}`}').toHaveClass('stock', `stock--${stockStatus(quantity)}`);
  });
});

describe('TODO 2: ProductCard', () => {
  test('shows the product it receives, not always Rice', () => {
    const sugar = byId(9);
    render(<ProductCard product={sugar} branch="NKW" />);
    expect(screen.getByRole('heading', { level: 3 })).toHaveTextContent(sugar.name);
    expect(screen.getByText(`${formatFcfa(sugar.price_fcfa)} / ${sugar.unit}`)).toBeInTheDocument();
  });

  test('the badge uses the stock at the branch it receives', () => {
    const palmOil = byId(14);
    const { container, rerender } = render(<ProductCard product={palmOil} branch="NKW" />);
    expect(container.querySelector('.stock').textContent).toBe(stockText(palmOil.stock.NKW));
    rerender(<ProductCard product={palmOil} branch="CAV" />);
    expect(container.querySelector('.stock').textContent).toBe(stockText(palmOil.stock.CAV));
  });

  test('a branch with no stock yet (Limbe) counts as 0', () => {
    const { container } = render(<ProductCard product={byId(6)} branch="LMB" />);
    expect(container.querySelector('.stock').textContent).toBe(stockText(0));
  });

  test('without a branch prop it uses Nkwen', () => {
    const maggi = byId(7);
    const { container } = render(<ProductCard product={maggi} />);
    expect(container.querySelector('.stock').textContent).toBe(stockText(maggi.stock.NKW));
  });

  test('never changes the product it receives (props are read-only)', () => {
    const frozen = Object.freeze({ ...byId(6), stock: Object.freeze({ ...byId(6).stock }) });
    expect(() => render(<ProductCard product={frozen} branch="ML4" />)).not.toThrow();
  });
});

describe('TODO 3: Panel', () => {
  test('shows its title and whatever you put inside it', () => {
    render(
      <Panel title="Opening hours">
        <p>Monday to Saturday, 7:30 to 19:00</p>
      </Panel>,
    );
    expect(screen.getByRole('heading', { level: 2, name: 'Opening hours' })).toBeInTheDocument();
    expect(screen.getByText(/Monday to Saturday/)).toBeInTheDocument();
  });
});

describe('TODO 4: App', () => {
  test('shows three cards, one with each kind of badge', () => {
    const { container } = render(<App />);
    expect(screen.getAllByRole('article')).toHaveLength(3);
    for (const status of ['in', 'low', 'out']) {
      expect(container.querySelectorAll(`.stock--${status}`), `one card should show stock--${status}`).toHaveLength(1);
    }
  });

  test('the branch details are inside a Panel', () => {
    render(<App />);
    expect(screen.getByRole('heading', { level: 2, name: 'Collect here' })).toBeInTheDocument();
  });
});
