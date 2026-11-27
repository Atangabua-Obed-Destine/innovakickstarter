import { NKWEN } from '../data/nkwen.js';
import { formatFcfa } from '../lib/format.js';

// The product of the week is Rice 50kg (id 6). In the next lesson the card
// will receive ANY product through "props". Today it always shows this one.
const product = NKWEN.products.find((p) => p.id === 6);

// TODO 3: return an <article> with the class "product-card" that shows:
// - the product name in an <h3>
// - the category in a <p> with the class "category"
// - the price and the unit in a <p> with the class "price", like: 600 FCFA / loaf
//   Use { } to put a JavaScript value into JSX, and formatFcfa() for the price.
export default function ProductCard() {
  return <p>TODO 3: ProductCard ({product.name})</p>;
}
