import { NKWEN } from '../data/nkwen.ts';
import { formatFcfa } from '../lib/format.ts';

// TODO 4: the product list, with each name as a link to its own page:
//   <Link to={`/products/${p.id}`}>{p.name}</Link>   (Link from 'react-router')
// Use <ul className="product-grid" aria-label="Products"> and one <li key={p.id}> each.
//
// TODO 6: keep the chosen category IN THE ADDRESS, like /?category=Oils, so the
// Back button and shared links work. Use useSearchParams() from 'react-router':
//   const [params, setParams] = useSearchParams();
//   const category = params.get('category') ?? 'All';
// Add a <select id="category"> labelled "Category" (All categories + one option per
// category). On change: setParams({ category: value }), or setParams({}) for All.
export default function ShopPage() {
  return (
    <>
      <h1>Shop</h1>
      <p>TODO 4: the products</p>
    </>
  );
}
