import { useEffect, useState } from 'react';
import { Link } from 'react-router';
import { buildUrl, getJson } from '../api/nkwen.ts';
import { formatFcfa } from '../lib/format.ts';
import type { Product, ProductsResponse } from '../types.ts';

// "Uneven network" practice mode: searches of 1 or 2 letters answer slowly
// (2 seconds), longer ones quickly. Real networks are uneven like this.
function practiceDelay(uneven: boolean, search: string): number | undefined {
  if (!uneven) return undefined;
  return search.trim().length <= 2 ? 2000 : 200;
}

export default function ShopPage() {
  const [search, setSearch] = useState('');
  const [uneven, setUneven] = useState(false);
  const [products, setProducts] = useState<Product[] | null>(null);
  const [error, setError] = useState('');

  const query = { search: search.trim(), delay: practiceDelay(uneven, search) };

  useEffect(() => {
    setError('');
    getJson<ProductsResponse>(buildUrl('/products', query))
      .then((data) => setProducts(data.products))
      .catch(() => setError('Sorry, we could not load the products.'));
  }, [query]);

  return (
    <>
      <h1>Shop</h1>
      <div className="filters">
        <div>
          <label htmlFor="search">Search</label>
          <input id="search" type="search" value={search} onChange={(e) => setSearch(e.target.value)} />
        </div>
        <div>
          <label>
            <input type="checkbox" checked={uneven} onChange={(e) => setUneven(e.target.checked)} />
            Practice: uneven network
          </label>
        </div>
      </div>
      {error && <p className="alert" role="alert">{error}</p>}
      {products === null && !error && <p className="empty">Loading products...</p>}
      {products && (
        <ul className="product-grid" aria-label="Products">
          {products.map((p) => (
            <li key={p.id} className="product-card">
              <h3><Link to={`/products/${p.id}`}>{p.name}</Link></h3>
              <p className="price">{formatFcfa(p.price_fcfa)}</p>
            </li>
          ))}
        </ul>
      )}
    </>
  );
}
