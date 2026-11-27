import { useState } from 'react';
import BasketSummary from './components/BasketSummary.jsx';
import ProductCard from './components/ProductCard.jsx';
import { NKWEN } from './data/nkwen.js';
import { addItem, maxAllowed, quantityOf, revalidate } from './lib/basket.js';

const openBranches = NKWEN.branches.filter((b) => b.status === 'open');
const branchName = (code) => openBranches.find((b) => b.code === code).name;

export default function App() {
  // TODO 3: replace these three constants with state:
  //   const [branch, setBranch] = useState('NKW');
  //   ...the same for basket (starts as []) and message (starts as '').
  const branch = 'NKW';
  const basket = [];
  const message = '';

  // TODO 4: the customer clicked Add on a product.
  // Work out the next basket with addItem(basket, product, branch).
  // - If addItem gave back the SAME basket, the limit is reached: set the message
  //   to say the most they can order (use maxAllowed) and stop.
  // - Otherwise set the new basket and a message such as "Rice 50kg added."
  function handleAdd(product) {
    console.log('TODO 4: add', product.name);
  }

  // TODO 5: empty the basket and say so in the message.
  function handleClear() {
    console.log('TODO 5: clear');
  }

  // TODO 6: the customer chose another branch in the drop-down.
  // event.target.value is the new code. Use revalidate(basket, NKWEN.products, code)
  // to fit the basket to that branch's stock. Set branch, basket and a message:
  //   "Now collecting at Commercial Avenue."  or, when lines changed,
  //   "Now collecting at Commercial Avenue. Stock is different here: Sugar 1kg removed."
  // (Use "reduced to N" for a line that got smaller.)
  function handleBranchChange(event) {
    console.log('TODO 6: branch', event.target.value, branchName(event.target.value));
  }

  return (
    <>
      <header className="site-header">
        <p className="logo">Nkwen Traders</p>
        <BasketSummary basket={basket} products={NKWEN.products} />
      </header>
      <main className="shop">
        <h1>Shop online, collect at your branch</h1>
        <div className="filters">
          <div>
            <label htmlFor="branch">Collect at</label>
            <select id="branch" value={branch} onChange={handleBranchChange}>
              {openBranches.map((b) => (
                <option key={b.code} value={b.code}>{b.name}</option>
              ))}
            </select>
          </div>
        </div>
        <p className="status" role="status">{message}</p>
        <button type="button" className="secondary" onClick={handleClear}>Empty the basket</button>
        <ul className="product-grid">
          {NKWEN.products.map((p) => (
            <li key={p.id}>
              {/* TODO 7: also pass inBasket={quantityOf(basket, p.id)} and onAdd={handleAdd} */}
              <ProductCard product={p} branch={branch} />
            </li>
          ))}
        </ul>
      </main>
    </>
  );
}
