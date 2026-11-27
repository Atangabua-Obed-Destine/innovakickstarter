import { photos } from '../data'
import { formatFcfa, stockStatus, stockText } from '../format'
import type { BranchCode, Product } from '../types'

type Props = { product: Product; branch: BranchCode; onAdd: (product: Product) => void }

// A small product card under "You may also need".
export function ProductTile({ product, branch, onAdd }: Props) {
  const quantity = product.stock[branch]

  return (
    <div className="tile">
      <img src={photos[product.id]} />
      <p>
        <b>{product.name}</b>
      </p>
      <p>{formatFcfa(product.price_fcfa)}</p>
      <p className={`badge-${stockStatus(quantity)}`}>{stockText(quantity)}</p>
      <div className="add" onClick={() => onAdd(product)}>
        + Add
      </div>
    </div>
  )
}
