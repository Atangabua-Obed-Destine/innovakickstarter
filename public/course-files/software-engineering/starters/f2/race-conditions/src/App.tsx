import { NavLink, Route, Routes } from 'react-router';
import ProductPage from './pages/ProductPage.tsx';
import ShopPage from './pages/ShopPage.tsx';

export default function App() {
  return (
    <>
      <header className="site-header">
        <p className="logo">Nkwen Traders</p>
        <nav aria-label="Main" className="site-nav">
          <ul>
            <li><NavLink to="/" end>Shop</NavLink></li>
          </ul>
        </nav>
      </header>
      <main className="shop">
        <Routes>
          <Route index element={<ShopPage />} />
          <Route path="products/:id" element={<ProductPage />} />
        </Routes>
      </main>
    </>
  );
}
