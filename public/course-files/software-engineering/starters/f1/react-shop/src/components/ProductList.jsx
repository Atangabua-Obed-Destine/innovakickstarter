// TODO 11: start from your ProductList from the Lists & Keys lesson.
// Props: products, branch, basket, search, onAdd.
// - No products: a <p className="empty"> message that names the search words.
// - Otherwise <ul className="product-grid" aria-label="Products">, one <li key={p.id}> per product,
//   each holding <ProductCard product={p} branch={branch} inBasket={quantityOf(basket, p.id)} onAdd={onAdd} />
export default function ProductList({ products }) {
  return <p>TODO 11: ProductList ({products.length} products)</p>;
}
