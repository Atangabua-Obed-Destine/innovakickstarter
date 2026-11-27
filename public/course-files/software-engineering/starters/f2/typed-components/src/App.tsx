import { useState } from 'react';
import BranchSelect from './components/BranchSelect.tsx';
import Panel from './components/Panel.tsx';
import ProductCard from './components/ProductCard.tsx';
import QuantityPicker from './components/QuantityPicker.tsx';
import { NKWEN } from './data/nkwen.ts';
import type { Basket, OpenBranchCode, Product } from './types.ts';

const openBranches = NKWEN.branches.filter((b) => b.status === 'open');

export default function App() {
  // TODO 7: tell useState what it holds, with <...>:
  //   useState<OpenBranchCode>('NKW')   and   useState<Basket>([])
  // Without it TypeScript guesses: 'NKW' becomes just a string, and [] an array of nothing.
  const [branch, setBranch] = useState('NKW');
  const [basket, setBasket] = useState([]);
  const qtyOf = (id: number) => basket.find((line) => line.id === id)?.qty ?? 0;
  const limit = (product: Product) => Math.min(product.stock[branch] ?? 0, 10);

  function setQty(product: Product, qty: number) {
    const others = basket.filter((line) => line.id !== product.id);
    setBasket(qty > 0 ? [...others, { id: product.id, qty }] : others);
  }

  return (
    <main className="shop">
      <h1>The typed shop</h1>
      <Panel title="Where will you collect?">
        <BranchSelect branches={openBranches} value={branch} onChange={setBranch} />
      </Panel>
      <ul className="product-grid">
        {NKWEN.products.slice(0, 8).map((p) => (
          <li key={p.id}>
            <ProductCard product={p} branch={branch} inBasket={qtyOf(p.id)} onAdd={(product) => setQty(product, qtyOf(product.id) + 1)} />
            {qtyOf(p.id) > 0 && (
              <QuantityPicker label={`Quantity of ${p.name}`} value={qtyOf(p.id)} max={limit(p)} onChange={(qty) => setQty(p, qty)} />
            )}
          </li>
        ))}
      </ul>
    </main>
  );
}
