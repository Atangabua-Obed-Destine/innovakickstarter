import WholesaleForm from './components/WholesaleForm.jsx';
import { NKWEN } from './data/nkwen.js';

export default function App() {
  return (
    <>
      <header className="site-header">
        <p className="logo">Nkwen Traders</p>
      </header>
      <main className="shop">
        <h1>Wholesale price request</h1>
        <p>Shops and restaurants that buy 10 units or more: tell us what you need and we will call you back.</p>
        <WholesaleForm products={NKWEN.products} branches={NKWEN.branches.filter((b) => b.status === 'open')} />
      </main>
    </>
  );
}
