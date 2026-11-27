import { formatFcfa } from '../lib/format.js';

const CHOICES = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

export default function BasketLine({ line, onSetQty, onRemove }) {
  const { product } = line;
  return (
    <li className="basket-line">
      <span className="line-name">{product.name}</span>
      <span className="line-total">{formatFcfa(product.price_fcfa * line.qty)}</span>
      <div className="qty-controls">
        <label>
          <span className="visually-hidden">Quantity of {product.name}</span>
          <select value={line.qty} onChange={(event) => onSetQty(line.id, event.target.value)}>
            {CHOICES.map((n) => (
              <option key={n} value={n}>{n}</option>
            ))}
          </select>
        </label>
        <button type="button" className="remove" onClick={onRemove(line.id)}>
          Remove{' '}<span className="visually-hidden">{product.name}</span>
        </button>
      </div>
    </li>
  );
}
