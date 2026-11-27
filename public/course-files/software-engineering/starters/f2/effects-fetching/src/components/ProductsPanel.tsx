import { buildUrl, practiceParams, type PracticeMode } from '../api/nkwen.ts';
import { useApi } from '../hooks/useApi.ts';
import { formatFcfa, stockText } from '../lib/format.ts';
import type { Branch, ProductsResponse } from '../types.ts';

interface ProductsPanelProps {
  branch: Branch;
  mode: PracticeMode;
}

// TODO 2: show the products in stock at one branch, in FOUR clear states.
// 1. Build the URL with buildUrl('/products', { branch: branch.code, ...practiceParams(mode) })
//    and call useApi<ProductsResponse>(url). Call the hook FIRST, before any "return":
//    React needs the same hooks in the same order on every render.
// 2. Then return ONE of these:
//    - planned branch (Limbe): <p className="empty" role="status">Limbe Down Beach is not open yet. It opens in 2027.</p>
//    - loading: <p className="empty" role="status">Loading products...</p>
//    - error:   <div className="alert" role="alert"> with a sentence, the message, and a
//               "Try again" button that calls retry
//    - ready but no products: <p className="empty" role="status">No products in stock at this branch.</p>
//    - ready:   <ul className="product-grid" aria-label="Products">, one <li key={p.id}> per product:
//               name, formatFcfa(price), and stockText(stock at this branch)
export default function ProductsPanel({ branch, mode }: ProductsPanelProps) {
  return <p className="empty">TODO 2: products for {branch.name} ({mode})</p>;
}
