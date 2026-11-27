import { useBasket } from '../basket/BasketContext.tsx';
import { NKWEN } from '../data/nkwen.ts';
import { formatFcfa, stockText } from '../lib/format.ts';

// Ready for you: reads AND changes the shared basket, with no props at all.
export default function ProductList() {
  const { state, dispatch } = useBasket();
  return (
    <ul className="product-grid" aria-label="Products">
      {NKWEN.products.map((p) => (
        <li key={p.id} className="product-card">
          <h3>{p.name}</h3>
          <p className="price">{formatFcfa(p.price_fcfa)} / {p.unit}</p>
          <p className="stock">{stockText(p.stock[state.branch] ?? 0)}</p>
          <button type="button" onClick={() => dispatch({ type: 'add', product: p })}>
            Add{' '}<span className="visually-hidden">{p.name}</span>
          </button>
        </li>
      ))}
    </ul>
  );
}
