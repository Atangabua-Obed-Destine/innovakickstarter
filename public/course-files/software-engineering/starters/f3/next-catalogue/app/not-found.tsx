import Link from 'next/link'

// Shown, with status 404, when notFound() is called or the address does not exist.
export default function NotFound() {
  return (
    <main>
      <h1>We could not find that page</h1>
      <p>
        <Link href="/">Back to all products</Link>
      </p>
    </main>
  )
}
