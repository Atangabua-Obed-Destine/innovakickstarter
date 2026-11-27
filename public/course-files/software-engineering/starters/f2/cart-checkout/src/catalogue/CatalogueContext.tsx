import { createContext, useContext, type ReactNode } from 'react';
import { buildUrl, practiceParams, type PracticeMode } from '../api/nkwen.ts';
import { useApi } from '../hooks/useApi.ts';
import type { Branch, Product, ProductsResponse, BranchesResponse } from '../types.ts';

// Ready for you: loads the products and branches ONCE for every page, with YOUR
// useApi hook, and shares them through a context (like the basket).
export interface Catalogue {
  status: 'loading' | 'error' | 'ready';
  message: string;
  products: Product[];
  branches: Branch[]; // open branches only
  retry: () => void;
}

const CatalogueContext = createContext<Catalogue | null>(null);

export function CatalogueProvider({ mode, children }: { mode: PracticeMode; children: ReactNode }) {
  const products = useApi<ProductsResponse>(buildUrl('/products', practiceParams(mode)));
  const branches = useApi<BranchesResponse>(buildUrl('/branches', practiceParams(mode)));
  const retry = () => {
    products.retry();
    branches.retry();
  };
  let value: Catalogue;
  if (products.status === 'ready' && branches.status === 'ready') {
    value = { status: 'ready', message: '', products: products.data.products, branches: branches.data.branches.filter((b) => b.status === 'open'), retry };
  } else if (products.status === 'error' || branches.status === 'error') {
    value = { status: 'error', message: products.status === 'error' ? products.message : branches.status === 'error' ? branches.message : '', products: [], branches: [], retry };
  } else {
    value = { status: 'loading', message: '', products: [], branches: [], retry };
  }
  return <CatalogueContext value={value}>{children}</CatalogueContext>;
}

export function useCatalogue(): Catalogue {
  const value = useContext(CatalogueContext);
  if (value === null) throw new Error('useCatalogue must be used inside <CatalogueProvider>.');
  return value;
}

// Shows loading or error for pages that need the catalogue. Returns null when ready.
export function CatalogueState() {
  const catalogue = useCatalogue();
  if (catalogue.status === 'loading') return <p className="empty" role="status">Loading products...</p>;
  if (catalogue.status === 'error') {
    return (
      <div className="alert" role="alert">
        <p>Sorry, we could not load the shop. {catalogue.message}</p>
        <button type="button" onClick={catalogue.retry}>Try again</button>
      </div>
    );
  }
  return null;
}
