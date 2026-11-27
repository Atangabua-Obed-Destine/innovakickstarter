import { useState } from 'react'
import { BasketRow } from './components/BasketRow'
import { PaymentForm } from './components/PaymentForm'
import { ProductTile } from './components/ProductTile'
import { matches, palmOil, rice, soap, sugar } from './data'
import { formatFcfa } from './format'
import type { BasketLine, Product } from './types'

// The checkout page for the Nkwen branch. Customer Mrs. Mbah Clarisse uses a screen reader.
export function CheckoutPage() {
  const [lines, setLines] = useState<BasketLine[]>([
    { product: rice, quantity: 2 },
    { product: palmOil, quantity: 1 },
  ])

  function add(product: Product) {
    const found = lines.find((line) => line.product.id === product.id)
    if (found) {
      setLines(lines.map((line) => (line === found ? { ...line, quantity: line.quantity + 1 } : line)))
    } else {
      setLines([...lines, { product, quantity: 1 }])
    }
  }

  const total = lines.reduce((sum, line) => sum + line.product.price_fcfa * line.quantity, 0)

  return (
    <div className="page">
      <h1>Checkout</h1>

      <h4>Your basket</h4>
      <ul>
        {lines.map((line) => (
          <BasketRow key={line.product.id} line={line} onRemove={(id) => setLines(lines.filter((l) => l.product.id !== id))} />
        ))}
      </ul>
      <p>
        <b>Total: {formatFcfa(total)}</b> (VAT included)
      </p>

      <h4>You may also need</h4>
      <div className="tiles">
        {[sugar, matches, soap].map((product) => (
          <ProductTile key={product.id} product={product} branch="NKW" onAdd={add} />
        ))}
      </div>

      <h4>Pay</h4>
      <PaymentForm total={total} />
    </div>
  )
}
