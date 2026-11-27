import { useState } from 'react'
import { BasketSummary } from './components/BasketSummary'
import { ProductList } from './components/ProductList'
import type { BasketLine, Product } from './types'

// A tiny shop: the basket and the product list. Your real shop has more pages.
export default function App() {
  const [lines, setLines] = useState<BasketLine[]>([])

  function add(product: Product) {
    const found = lines.find((line) => line.product.id === product.id)
    if (found) {
      setLines(lines.map((line) => (line === found ? { ...line, quantity: line.quantity + 1 } : line)))
    } else {
      setLines([...lines, { product, quantity: 1 }])
    }
  }

  function remove(productId: number) {
    setLines(lines.filter((line) => line.product.id !== productId))
  }

  return (
    <main>
      <h1>Nkwen Traders shop</h1>
      <p>Prices and stock for the Commercial Avenue branch.</p>
      <BasketSummary lines={lines} onRemove={remove} />
      <ProductList branch="CAV" onAdd={add} />
    </main>
  )
}
