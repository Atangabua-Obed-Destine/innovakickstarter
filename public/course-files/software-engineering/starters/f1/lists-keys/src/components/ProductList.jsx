import ProductCard from './ProductCard.jsx';

// TODO 2: draw the list of products.
// - When products is empty, return ONLY a message, in a <p className="empty" role="status">:
//     with a search:    No products match "zzz". Try another word or category.
//     without a search: No products in this category.
//   (Use search.trim() so spaces do not count.)
// - Otherwise return <ul className="product-grid" aria-label="Products"> with one
//   <li> per product. Use products.map(...). Each <li> needs a key: the product id.
//   Inside each <li>: <ProductCard product={p} branch={branch} />
export default function ProductList({ products, branch, search = '' }) {
  return <p>TODO 2: ProductList ({products.length} products)</p>;
}
