import { useEffect, useState } from 'react'
import { API_URL } from '../api'
import type { BranchCode, Product } from '../types'
import { ProductCard } from './ProductCard'

type Props = {
  branch: BranchCode
  onAdd: (product: Product) => void
}

// Loads the products from the API, then shows a search box and one card per product.
export function ProductList({ branch, onAdd }: Props) {
  const [products, setProducts] = useState<Product[]>([])
  const [status, setStatus] = useState<'loading' | 'error' | 'ready'>('loading')
  const [attempt, setAttempt] = useState(0)
  const [query, setQuery] = useState('')

  useEffect(() => {
    let ignore = false
    setStatus('loading')
    fetch(`${API_URL}/products`)
      .then((response) => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`)
        return response.json()
      })
      .then((data: { products: Product[] }) => {
        if (ignore) return
        setProducts(data.products)
        setStatus('ready')
      })
      .catch(() => {
        if (!ignore) setStatus('error')
      })
    return () => {
      ignore = true
    }
  }, [attempt])

  if (status === 'loading') {
    return <p role="status">Loading products…</p>
  }

  if (status === 'error') {
    return (
      <div role="alert">
        <p>Sorry, we could not load the products. Check your connection.</p>
        <button type="button" onClick={() => setAttempt(attempt + 1)}>
          Try again
        </button>
      </div>
    )
  }

  // Mrs. Fon Lydia's bug report: "I typed RICE and the shop found nothing."
  const shown = products.filter((product) => product.name.includes(query))

  return (
    <section aria-labelledby="products-heading">
      <h2 id="products-heading">Products</h2>
      <label htmlFor="search">Search products</label>
      <input id="search" type="search" value={query} onChange={(event) => setQuery(event.target.value)} />
      <p>{shown.length} products</p>
      {shown.length === 0 ? (
        <p>No products match "{query}".</p>
      ) : (
        <ul className="product-grid">
          {shown.map((product) => (
            <li key={product.id}>
              <ProductCard product={product} branch={branch} onAdd={onAdd} />
            </li>
          ))}
        </ul>
      )}
    </section>
  )
}
