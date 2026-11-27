import { formatFcfa, stockStatus, stockText } from '../format'
import type { BranchCode, Product } from '../types'

type Props = {
  product: Product
  branch: BranchCode
  onAdd: (product: Product) => void
}

// One product in the catalogue: name, price, stock badge and an Add button.
export function ProductCard({ product, branch, onAdd }: Props) {
  const quantity = product.stock[branch]
  const status = stockStatus(quantity)

  return (
    <article className="product-card">
      <h3>{product.name}</h3>
      <p className="price">{formatFcfa(product.price_fcfa)}</p>
      <p className={`badge badge-${status}`}>{stockText(quantity)}</p>
      <button type="button" onClick={() => onAdd(product)} disabled={status === 'out'}>
        Add <span className="visually-hidden">{product.name}</span> to basket
      </button>
    </article>
  )
}
