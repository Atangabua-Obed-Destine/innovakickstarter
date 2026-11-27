import { useState } from 'react';
import CategoryChips from './components/CategoryChips.jsx';
import ProductList from './components/ProductList.jsx';
import ResultCount from './components/ResultCount.jsx';
import { NKWEN } from './data/nkwen.js';
import { filterProducts } from './lib/catalogue.js';

// This file is ready. It keeps the search box, the category and the
// "Cheapest first" box in state (you learn useState in "State & Events").
// Your work today is in the components it uses.
export default function App() {
  const [search, setSearch] = useState('');
  const [category, setCategory] = useState('All');
  const [cheapestFirst, setCheapestFirst] = useState(false);

  const filtered = filterProducts(NKWEN.products, { search, category });
  const visible = cheapestFirst ? [...filtered].sort((a, b) => a.price_fcfa - b.price_fcfa) : filtered;

  return (
    <main className="shop">
      <h1>Find a product at Nkwen</h1>
      <form className="filters" role="search" aria-label="Find products" onSubmit={(e) => e.preventDefault()}>
        <div>
          <label htmlFor="search">Search</label>
          <input id="search" type="search" value={search} onChange={(e) => setSearch(e.target.value)} />
        </div>
        <div>
          <label htmlFor="category">Category</label>
          <select id="category" value={category} onChange={(e) => setCategory(e.target.value)}>
            <option value="All">All categories</option>
            {NKWEN.categories.map((c) => (
              <option key={c} value={c}>{c}</option>
            ))}
          </select>
        </div>
        <div>
          <label>
            <input type="checkbox" checked={cheapestFirst} onChange={(e) => setCheapestFirst(e.target.checked)} />
            Cheapest first
          </label>
        </div>
      </form>
      <ResultCount count={visible.length} />
      <CategoryChips products={visible} categories={NKWEN.categories} />
      <ProductList products={visible} branch="NKW" search={search} />
    </main>
  );
}
