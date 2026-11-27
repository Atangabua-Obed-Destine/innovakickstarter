// TODO 3 (user stories 3 and 4): the shop.
// - useCatalogue() for the products and branches; show <CatalogueState /> while loading or on error.
// - useBasket() for the shared basket. A "Collect at" select (label words exactly "Collect at")
//   dispatches { type: 'changeBranch', branch, branchName, products } and the status line
//   (<p role="status">) shows state.message.
// - A "Search" box that filters by name, and a "No products match" message.
// - <ul aria-label="Products"> of your ProductCard, with inBasket and onAdd (dispatch 'add').
// - Each product name links to /products/<id> (put a <Link> inside your ProductCard's <h3>).
export default function ShopPage() {
  return <h1>TODO: ShopPage</h1>;
}
