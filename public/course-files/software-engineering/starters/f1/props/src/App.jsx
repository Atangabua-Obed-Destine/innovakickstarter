import Panel from './components/Panel.jsx';
import ProductCard from './components/ProductCard.jsx';
import { NKWEN } from './data/nkwen.js';

const BRANCH = 'NKW';
const branch = NKWEN.branches.find((b) => b.code === BRANCH);
const byId = (id) => NKWEN.products.find((p) => p.id === id);

// Three products, one for each stock badge at Nkwen.
const rice = byId(6);
const maggi = byId(7);
const detergent = byId(11);

export default function App() {
  return (
    <main className="shop">
      <h1>This week at {branch.name}</h1>
      {/* TODO 4a: put a <Panel title="Collect here"> around this paragraph. */}
      <p>
        {branch.name}, {branch.city}. Manager: {branch.manager}.
      </p>
      <ul className="product-grid">
        {/* TODO 4b: show three cards, one in each <li>: rice, maggi and detergent.
            Pass each card its product and the branch:
            <ProductCard product={rice} branch={BRANCH} />  */}
        <li>{rice.name}</li>
        <li>{maggi.name}</li>
        <li>{detergent.name}</li>
      </ul>
    </main>
  );
}
