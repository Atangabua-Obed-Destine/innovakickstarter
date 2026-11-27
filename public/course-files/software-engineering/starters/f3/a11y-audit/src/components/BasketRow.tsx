import { formatFcfa } from '../format'
import type { BasketLine } from '../types'

type Props = { line: BasketLine; onRemove: (productId: number) => void }

// One line of the basket, with a round "remove" button.
export function BasketRow({ line, onRemove }: Props) {
  const { product, quantity } = line

  return (
    <li>
      {quantity} x {product.name}: {formatFcfa(product.price_fcfa * quantity)}{' '}
      <button type="button" className="icon-button" onClick={() => onRemove(product.id)}>
        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16">
          <path d="M3 3 L13 13 M13 3 L3 13" stroke="currentColor" strokeWidth="2" />
        </svg>
      </button>
    </li>
  )
}
