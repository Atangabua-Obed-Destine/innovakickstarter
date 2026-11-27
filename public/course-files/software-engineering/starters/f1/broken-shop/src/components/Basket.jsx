import { formatFcfa } from '../lib/format.js';
import BasketLine from './BasketLine.jsx';

export default function Basket({ basket, products, onSetQty, onRemove }) {
  const lines = basket.map((line) => ({ ...line, product: products.find((p) => p.id === line.id) }));
  const total = lines.reduce((sum, line) => sum + line.product.price_fcfa * line.qty, 0);
  return (
    <aside className="basket" aria-labelledby="basket-title">
      <h2 id="basket-title">Your basket</h2>
      {lines.length === 0 ? (
        <p className="empty">Nothing here yet.</p>
      ) : (
        <ul>
          {lines.map((line) => (
            <BasketLine key={line.id} line={line} onSetQty={onSetQty} onRemove={onRemove} />
          ))}
        </ul>
      )}
      <p className="total">
        Total: <strong>{formatFcfa(total)}</strong>
      </p>
    </aside>
  );
}
