import { useBasket } from '../basket/BasketContext.tsx';
import { NKWEN } from '../data/nkwen.ts';
import { basketLines, basketTotal } from '../lib/basket.ts';
import { formatFcfa } from '../lib/format.ts';

// Ready for you: a second component, far from ProductList, using the same basket.
export default function BasketPanel() {
  const { state, dispatch } = useBasket();
  const lines = basketLines(state.lines, NKWEN.products);
  const productOf = (id: number) => NKWEN.products.find((p) => p.id === id)!;
  return (
    <aside className="basket" aria-labelledby="basket-title">
      <h2 id="basket-title">Your basket</h2>
      {lines.length === 0 && <p className="empty">Your basket is empty.</p>}
      <ul>
        {lines.map((line) => (
          <li key={line.id} className="basket-line">
            <span className="line-name">{line.qty} x {line.name}</span>
            <span className="line-total">{formatFcfa(line.line_total)}</span>
            <button type="button" className="secondary" onClick={() => dispatch({ type: 'remove', product: productOf(line.id) })}>
              Remove{' '}<span className="visually-hidden">{line.name}</span>
            </button>
          </li>
        ))}
      </ul>
      <p className="total">Total: <strong>{formatFcfa(basketTotal(state.lines, NKWEN.products))}</strong></p>
      <button type="button" className="secondary" onClick={() => dispatch({ type: 'clear' })}>Empty the basket</button>
    </aside>
  );
}
