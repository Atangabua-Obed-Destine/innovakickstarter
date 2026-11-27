import { formatFcfa } from '../lib/format.ts';
import StockBadge from './StockBadge.tsx';

// TODO 4: type the props. There are four:
//   product   a Product   (import type { OpenBranchCode, Product } from '../types.ts')
//   branch    an OpenBranchCode
//   inBasket  a number, optional, 0 when missing   (inBasket?: number, plus the default below)
//   onAdd     a function that receives a Product and returns nothing: (product: Product) => void
export default function ProductCard({ product, branch, inBasket = 0, onAdd }) {
  const quantity = product.stock[branch] ?? 0;
  const full = inBasket >= Math.min(quantity, 10);
  return (
    <article className="product-card">
      <h3>{product.name}</h3>
      <p className="price">{formatFcfa(product.price_fcfa)} / {product.unit}</p>
      <StockBadge quantity={quantity} />
      <button type="button" disabled={full} onClick={() => onAdd(product)}>
        {quantity === 0 ? 'Out of stock' : full ? 'Limit reached' : 'Add'}
        {' '}<span className="visually-hidden">{product.name}</span>
      </button>
    </article>
  );
}
