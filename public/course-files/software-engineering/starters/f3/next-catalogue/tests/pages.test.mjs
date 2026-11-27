// Checks the HTML your Next.js server sends, BEFORE any JavaScript runs in a browser.
// That HTML is what a search engine reads, and what a 3G phone shows first.
// 1. npm run build   2. npm start   3. in a second terminal: npm test
// To check your live site instead:  BASE_URL=https://your-app.vercel.app npm test  (PowerShell: $env:BASE_URL="..."; npm test)
import assert from 'node:assert/strict'
import { test } from 'node:test'

const BASE = (process.env.BASE_URL || 'http://localhost:3000').replace(/\/+$/, '')

async function page(path) {
  let response
  try {
    response = await fetch(BASE + path)
  } catch {
    assert.fail(`Could not reach ${BASE}. Is "npm start" running in another terminal?`)
  }
  return { status: response.status, html: await response.text() }
}

test('the home page HTML already lists the products', async () => {
  const { status, html } = await page('/')
  assert.equal(status, 200)
  assert.ok(html.includes('Rice 50kg'), 'The HTML has no "Rice 50kg". Does app/page.tsx get the products and list them?')
  assert.ok(html.includes('36,005 FCFA'), 'The HTML has no "36,005 FCFA". Did you use formatFcfa() for the prices?')
  const links = html.match(/href="\/products\/\d+"/g) || []
  assert.equal(new Set(links).size, 20, `Expected links to 20 product pages, found ${new Set(links).size}.`)
})

test('a product page has its own title, price and button in the HTML', async () => {
  const { status, html } = await page('/products/6')
  assert.equal(status, 200)
  assert.match(html, /<title>Rice 50kg \| Nkwen Traders<\/title>/, 'The <title> should be "Rice 50kg | Nkwen Traders" (generateMetadata).')
  assert.ok(html.includes('36,005 FCFA'), 'The product page should show the price.')
  assert.match(html, /<button[^>]*>[^<]*Add/, 'The product page should have the Add to basket button.')
  assert.ok(html.includes('Back to all products'), 'The product page should link back to the list.')
})

test('a product that does not exist answers 404', async () => {
  const { status, html } = await page('/products/999')
  assert.equal(status, 404, 'Call notFound() when getProduct() returns null.')
  assert.ok(html.includes('We could not find that page'))
})
