import { formatFcfa } from '../lib/format.js';
import StockBadge from './StockBadge.jsx';

// TODO 2: start from your ProductCard from the Components & JSX lesson,
// but now it receives two props instead of always showing Rice 50kg:
//   product  - one product object from the data
//   branch   - a branch code such as 'NKW'. Give it a default: branch = 'NKW'
// Show the name (<h3>), the category, the price with its unit,
// and then <StockBadge quantity={...} /> with the stock at THAT branch.
// A branch with no stock entry counts as 0:  product.stock[branch] ?? 0
// Props are read-only: never change product (no product.name = ...).
export default function ProductCard() {
  return <p>TODO 2: ProductCard</p>;
}
