import { useEffect, useState } from 'react';
import Basket from './components/Basket.jsx';
import Header from './components/Header.jsx';
import ProductList from './components/ProductList.jsx';
import { loadProducts } from './data/loadProducts.js';

// Written in a hurry by a colleague. Customers say the shop is broken.
// The tests in shop.test.jsx describe what it SHOULD do: they are correct.
export default function App() {
  const [products, setProducts] = useState();
  const [basket, setBasket] = useState([]);

  // Load the products once, when the page opens.
  useEffect(() => {
    loadProducts().then(setProducts);
  }, []);

  function handleAdd(product) {
    const line = basket.find((l) => l.id === product.id);
    if (line) {
      line.qty = line.qty + 1;
    } else {
      basket.push({ id: product.id, qty: 1 });
    }
    setBasket(basket);
  }

  function handleSetQty(id, qty) {
    setBasket(basket.map((l) => (l.id === id ? { ...l, qty } : l)));
  }

  function handleRemove(id) {
    setBasket(basket.filter((l) => l.id !== id));
  }

  return (
    <>
      <Header basket={basket} />
      <main className="shop">
        <h1>Shop online, collect at Nkwen</h1>
        <div className="layout">
          <ProductList products={products} onAdd={handleAdd} />
          <Basket basket={basket} products={products} onSetQty={handleSetQty} onRemove={handleRemove} />
        </div>
      </main>
    </>
  );
}
