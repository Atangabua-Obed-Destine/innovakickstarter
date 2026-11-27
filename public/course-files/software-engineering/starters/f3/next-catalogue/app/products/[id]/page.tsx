// One product page: /products/6, /products/15, ...
// The folder name [id] means "any value here"; Next.js gives it to the page as params.id.
import type { Metadata } from 'next'
import { getProducts } from '@/lib/api'

// Build all 20 product pages at build time, so they are ready before anyone asks.
// (Without this, "next build" stops: Next.js cannot know which ids to build.)
export async function generateStaticParams() {
  const products = await getProducts()
  return products.map((product) => ({ id: String(product.id) }))
}

// TODO 3: give each page its own title, for example "Rice 50kg | Nkwen Traders".
// Use getProduct(Number(id)) from '@/lib/api'.
export async function generateMetadata(props: PageProps<'/products/[id]'>): Promise<Metadata> {
  const { id } = await props.params
  return { title: `Product ${id} | Nkwen Traders` }
}

export default async function ProductPage(props: PageProps<'/products/[id]'>) {
  const { id } = await props.params
  // TODO 4: get the product with getProduct(Number(id)). If it is null, call notFound() from 'next/navigation'.
  // TODO 5: show an <h1> with the name, the price with formatFcfa(), the unit and the category,
  //         the <AddToBasket name={...} /> button, and a <Link href="/">Back to all products</Link>.
  return (
    <main>
      <h1>Product {id}</h1>
      <p>TODO: show this product (README step 5).</p>
    </main>
  )
}
