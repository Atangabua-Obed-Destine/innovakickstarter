import { formatFcfa } from '../lib/format.js';

export default function ProductList({ products, onAdd }) {
  if (products.length === 0) return <p role="status">Loading products...</p>;
  return (
    <section aria-labelledby="products-title">
      <h2 id="products-title">Products</h2>
      <ul className="product-grid">
        {products.map((p) => (
          <li key={p.id}>
            <article className="product-card">
              <h3>{p.name}</h3>
              <p className="price">{formatFcfa(p.price_fcfa)} / {p.unit}</p>
              <button type="button" onClick={() => onAdd(p)}>
                Add{' '}<span className="visually-hidden">{p.name}</span>
              </button>
            </article>
          </li>
        ))}
      </ul>
    </section>
  );
}
