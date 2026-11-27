import { basketTotal, itemCount } from '../lib/basket.js';
import { formatFcfa } from '../lib/format.js';

// TODO 8: show the basket in one line, worked out from the props every time:
//   <p className="basket-badge">Basket: 2 items, 1,200 FCFA</p>
// Use itemCount(basket) and formatFcfa(basketTotal(basket, products)).
// Do NOT keep the count or the total in their own state: they come from the basket.
export default function BasketSummary({ basket, products }) {
  return <p className="basket-badge">TODO 8: Basket summary ({basket.length} lines of {products.length} products)</p>;
}
