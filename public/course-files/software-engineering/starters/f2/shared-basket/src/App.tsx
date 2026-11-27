import { BasketProvider, useBasket } from './basket/BasketContext.tsx';
import BasketPanel from './components/BasketPanel.tsx';
import BranchSelect from './components/BranchSelect.tsx';
import Header from './components/Header.tsx';
import ProductList from './components/ProductList.tsx';

// The status line also reads the shared basket.
function StatusLine() {
  const { state } = useBasket();
  return <p className="status" role="status">{state.message}</p>;
}

// Ready for you. Look: no basket props anywhere. Everything inside
// <BasketProvider> can reach the basket with useBasket().
export default function App() {
  return (
    <BasketProvider>
      <Header />
      <main className="shop">
        <h1>One basket, everywhere</h1>
        <BranchSelect />
        <StatusLine />
        <div className="layout">
          <ProductList />
          <BasketPanel />
        </div>
      </main>
    </BasketProvider>
  );
}
