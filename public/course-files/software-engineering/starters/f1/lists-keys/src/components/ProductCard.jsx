import { formatFcfa } from '../lib/format.js';
import StockBadge from './StockBadge.jsx';

// Ready for you. Look at the ternary ( condition ? A : B ) near the end:
// a product in stock gets a quantity box, one out of stock gets a note.
// The quantity box is "uncontrolled": the browser remembers what you type.
// That makes it perfect for seeing what keys do (step 7 of the lesson).
export default function ProductCard({ product, branch }) {
  const quantity = product.stock[branch] ?? 0;
  return (
    <article className="product-card">
      <h3>{product.name}</h3>
      <p className="category">{product.category}</p>
      <p className="price">{formatFcfa(product.price_fcfa)} / {product.unit}</p>
      <StockBadge quantity={quantity} />
      {quantity > 0 ? (
        <label>
          Quantity{' '}<span className="visually-hidden">for {product.name}</span>
          <input type="number" min="1" defaultValue="1" />
        </label>
      ) : (
        <p className="empty">Ask at the counter</p>
      )}
    </article>
  );
}
