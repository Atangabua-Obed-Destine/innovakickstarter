import Layout from './components/Layout.tsx';
import BranchesPage from './pages/BranchesPage.tsx';
import NotFoundPage from './pages/NotFoundPage.tsx';
import ProductPage from './pages/ProductPage.tsx';
import ShopPage from './pages/ShopPage.tsx';

// TODO 2: the route table. Import Routes and Route from 'react-router'.
// One parent route with element={<Layout />} and, inside it:
//   index                 -> <ShopPage />
//   path "products/:id"   -> <ProductPage />    (":id" is a URL parameter)
//   path "branches"       -> <BranchesPage />
//   path "*"              -> <NotFoundPage />   ("*" matches any other address)
export default function App() {
  return (
    <>
      <p>TODO 2: the routes. Until then you only see the shop.</p>
      <ShopPage />
    </>
  );
}
