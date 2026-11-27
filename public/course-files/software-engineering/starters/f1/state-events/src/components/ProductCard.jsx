import { maxAllowed } from '../lib/basket.js';
import { formatFcfa } from '../lib/format.js';
import StockBadge from './StockBadge.jsx';

// TODO 2: the card gets two new props:
//   inBasket - how many of this product are already in the basket (default 0)
//   onAdd    - a function from App. Call onAdd(product) when the button is clicked.
// Add a <button type="button"> at the end of the card:
//   - onClick={() => onAdd(product)}   (pass a function; do NOT write onClick={onAdd(product)})
//   - disabled when inBasket has reached maxAllowed(product, branch)
//   - its words: "Out of stock" when the branch has none, "Limit reached" when
//     disabled for the limit, otherwise "Add"
//   - after the words, add {' '}<span className="visually-hidden">{product.name}</span>
//     so a screen reader hears "Add Rice 50kg", not just "Add".
export default function ProductCard({ product, branch = 'NKW' }) {
  const quantity = product.stock[branch] ?? 0;
  return (
    <article className="product-card">
      <h3>{product.name}</h3>
      <p className="price">{formatFcfa(product.price_fcfa)} / {product.unit}</p>
      <StockBadge quantity={quantity} />
      {/* TODO 2: the Add button goes here */}
    </article>
  );
}
