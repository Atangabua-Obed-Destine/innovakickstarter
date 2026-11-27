import { formatFcfa } from '../format'
import type { BasketLine } from '../types'

type Props = {
  lines: BasketLine[]
  onRemove: (productId: number) => void
}

// The basket: one row per product, a Remove button for each, and the total.
export function BasketSummary({ lines, onRemove }: Props) {
  if (lines.length === 0) {
    return (
      <section aria-labelledby="basket-heading">
        <h2 id="basket-heading">Your basket</h2>
        <p>Your basket is empty.</p>
      </section>
    )
  }

  const total = lines.reduce((sum, line) => sum + line.product.price_fcfa * line.quantity, 0)

  return (
    <section aria-labelledby="basket-heading">
      <h2 id="basket-heading">Your basket</h2>
      <ul>
        {lines.map((line) => (
          <li key={line.product.id}>
            {line.quantity} x {line.product.name}: {formatFcfa(line.product.price_fcfa * line.quantity)}{' '}
            <button type="button" onClick={() => onRemove(line.product.id)}>
              Remove <span className="visually-hidden">{line.product.name}</span>
            </button>
          </li>
        ))}
      </ul>
      <p className="total">Total: {formatFcfa(total)}</p>
      <p className="vat-note">Prices include VAT (19.25%).</p>
    </section>
  )
}
