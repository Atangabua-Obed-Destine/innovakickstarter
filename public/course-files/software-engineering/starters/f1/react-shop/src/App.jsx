import { useState } from 'react';
import { useCatalogue } from './api/useCatalogue.js';
import Basket from './components/Basket.jsx';
import Filters from './components/Filters.jsx';
import Header from './components/Header.jsx';
import ProductList from './components/ProductList.jsx';
import { addItem, basketTotal, itemCount, maxAllowed, quantityOf, removeItem, revalidate, setQuantity } from './lib/basket.js';
import { filterProducts } from './lib/catalogue.js';
import { formatFcfa } from './lib/format.js';
import { clearBasket, loadBasket, loadBranch, saveBasket, saveBranch } from './lib/storage.js';

// The four branches that are open today. A saved "LMB" (planned) falls back to Nkwen.
const OPEN_CODES = ['NKW', 'ML4', 'CAV', 'BAF'];

export default function App() {
  const [mode, setMode] = useState('normal');
  const catalogue = useCatalogue(mode); // { status, products, branches, categories, retry }
  const { products } = catalogue;

  // TODO 1: state for branch, basket, search, category and message.
  // branch and basket start from what was saved: pass a FUNCTION to useState so
  // it reads localStorage only once, e.g.  useState(() => loadBasket(localStorage))
  // Use loadBranch(localStorage, OPEN_CODES, 'NKW') for the branch.
  const branch = 'NKW';
  const basket = [];
  const search = '';
  const category = 'All';
  const message = '';

  // TODO 2: ONE function for every basket change: set the new basket,
  // save it with saveBasket(localStorage, next), and set the message.
  function changeBasket(next, words) {
    console.log('TODO 2', next, words);
  }

  // TODO 3: the handlers. Each one works out the next basket with your
  // Chapter 4 rules, then calls changeBasket. Messages as in Chapter 4, for example
  //   "Rice 50kg added. Basket total: 36,005 FCFA."   or the limit message.
  function handleAdd(product) {}
  function handleIncrease(id) {}
  function handleDecrease(id) {}
  function handleRemove(id) {}
  function handleClear() {}

  // TODO 4: changing branch: revalidate the basket, set and save the branch,
  // and say what changed ("Now collecting at Commercial Avenue. Stock is different here: ...").
  function handleBranchChange(code) {}

  // TODO 5: the products to show, from filterProducts(products, { search, category }).
  const visible = products;

  return (
    <>
      <Header count={0 /* TODO 6: the number of items in the basket */} />
      <main className="shop">
        <h1>Shop online, collect at your branch</h1>
        <Filters
          branches={catalogue.branches}
          categories={catalogue.categories}
          branch={branch}
          search={search}
          category={category}
          mode={mode}
          onBranchChange={handleBranchChange}
          onSearchChange={() => {} /* TODO 7: the setter for search */}
          onCategoryChange={() => {} /* TODO 7: the setter for category */}
          onModeChange={setMode}
        />
        <p className="status" role="status">{message}</p>
        <div className="layout">
          <section aria-labelledby="products-title">
            <h2 id="products-title">Products</h2>
            {/* TODO 8: show ONE of three things, depending on catalogue.status:
                'loading' -> <p className="empty">Loading products...</p>
                'error'   -> <div className="alert" role="alert"> with a sentence and a
                             "Try again" button that calls catalogue.retry
                'ready'   -> the ProductList below */}
            <ProductList products={visible} branch={branch} basket={basket} search={search} onAdd={handleAdd} />
          </section>
          <Basket
            basket={basket}
            products={products}
            branch={branch}
            onIncrease={handleIncrease}
            onDecrease={handleDecrease}
            onRemove={handleRemove}
            onClear={handleClear}
          />
        </div>
      </main>
    </>
  );
}

