import { useState } from 'react';
import { Route, Routes } from 'react-router';
import type { PracticeMode } from './api/nkwen.ts';
import { BasketProvider } from './basket/BasketContext.tsx';
import { CatalogueProvider } from './catalogue/CatalogueContext.tsx';
import Layout from './components/Layout.tsx';
import type { PlacedOrder } from './orderTypes.ts';
import BasketPage from './pages/BasketPage.tsx';
import CheckoutPage from './pages/CheckoutPage.tsx';
import DonePage from './pages/DonePage.tsx';
import NotFoundPage from './pages/NotFoundPage.tsx';
import PaymentPage from './pages/PaymentPage.tsx';
import ProductPage from './pages/ProductPage.tsx';
import ShopPage from './pages/ShopPage.tsx';

export default function App() {
  const [mode, setMode] = useState<PracticeMode>('normal');
  const [placed, setPlaced] = useState<PlacedOrder | null>(null);

  return (
    <CatalogueProvider mode={mode}>
      <BasketProvider>
        <Routes>
          <Route element={<Layout mode={mode} onModeChange={setMode} />}>
            <Route index element={<ShopPage />} />
            <Route path="products/:id" element={<ProductPage />} />
            <Route path="basket" element={<BasketPage />} />
            <Route path="checkout" element={<CheckoutPage mode={mode} onPlaced={setPlaced} />} />
            <Route path="pay" element={<PaymentPage placed={placed} />} />
            <Route path="done" element={<DonePage placed={placed} />} />
            <Route path="*" element={<NotFoundPage />} />
          </Route>
        </Routes>
      </BasketProvider>
    </CatalogueProvider>
  );
}
