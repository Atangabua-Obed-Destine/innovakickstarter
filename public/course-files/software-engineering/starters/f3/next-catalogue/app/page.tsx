// The home page: /
// This is a Server Component: it runs on the server, so it can await the API before sending any HTML.

export default async function HomePage() {
  // TODO 1: get the products with getProducts() from '@/lib/api'.
  // TODO 2: show an <h1>Our products</h1>, then a <ul> with one <li> per product.
  //         Each <li> has a <Link href={`/products/${product.id}`}> with the product name,
  //         and the price written with formatFcfa().
  return (
    <main>
      <h1>Our products</h1>
      <p>TODO: list the products here (README step 3).</p>
    </main>
  )
}
