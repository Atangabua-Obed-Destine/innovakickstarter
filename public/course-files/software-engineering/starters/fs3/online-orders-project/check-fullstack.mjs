// Checks that a React shop and a Laravel API can talk to each other, the way a browser would.
// Usage:  node check-fullstack.mjs <shop address> <API address>
//   live:     node check-fullstack.mjs https://nkwen-shop.netlify.app https://nkwen-api.onrender.com
//   rehearsal on your laptop: node check-fullstack.mjs http://localhost:4173 http://127.0.0.1:8000
// Needs Node.js 18 or newer.
const shop = (process.argv[2] || '').replace(/\/+$/, '')
const api = (process.argv[3] || '').replace(/\/+$/, '')
if (!shop.startsWith('http') || !api.startsWith('http')) {
  console.log('Give both addresses: node check-fullstack.mjs <shop address> <API address>')
  process.exit(1)
}
const local = (url) => /\/\/(localhost|127\.0\.0\.1)[:/]/.test(url + '/')

let passed = 0
let total = 0
function check(name, ok, hint) {
  total++
  if (ok) passed++
  console.log(`${ok ? 'PASS' : 'FAIL'}  ${name}${ok ? '' : `\n      - ${hint}`}`)
}
async function get(url, options = {}) {
  try {
    const response = await fetch(url, { ...options, signal: AbortSignal.timeout(70000) })
    return { status: response.status, headers: response.headers, text: await response.text() }
  } catch (error) {
    return { status: 0, headers: new Headers(), text: '', error: error.message }
  }
}

// 1. The API on its own.
const products = await get(`${api}/api/products`, { headers: { Accept: 'application/json' } })
let json = null
try { json = JSON.parse(products.text) } catch { /* not JSON */ }
check(
  'The API answers GET /api/products with JSON',
  products.status === 200 && Array.isArray(json?.data),
  products.error
    ? `Could not reach ${api} (${products.error}). Is it running? A free host may need a minute to wake up.`
    : `Got status ${products.status}${json ? '' : ', and the answer is not JSON (starts with: ' + products.text.slice(0, 50).replace(/\s+/g, ' ') + ')'}.`,
)

// 2. CORS for a simple GET from the shop's origin (an origin is scheme + host + port, with no path and no slash at the end).
const origin = new URL(shop).origin
const cors = await get(`${api}/api/products`, { headers: { Accept: 'application/json', Origin: origin } })
const allowed = cors.headers.get('access-control-allow-origin')
check(
  `The API lets ${origin} read its answers (CORS)`,
  allowed === origin || allowed === '*',
  allowed
    ? `The API allows "${allowed}", not "${origin}". Check FRONTEND_URL: exactly the shop's address, https, no slash at the end. Then clear the config cache.`
    : `No Access-Control-Allow-Origin header. Add ${origin} to FRONTEND_URL on the API, then restart it (php artisan config:clear on your laptop).`,
)

// 3. The preflight a browser sends before POST /api/orders with a token.
const preflight = await get(`${api}/api/orders`, {
  method: 'OPTIONS',
  headers: { Origin: origin, 'Access-Control-Request-Method': 'POST', 'Access-Control-Request-Headers': 'authorization,content-type' },
})
const allowHeaders = (preflight.headers.get('access-control-allow-headers') || '').toLowerCase()
const preflightOrigin = preflight.headers.get('access-control-allow-origin')
const originOk = [origin, '*'].includes(preflightOrigin)
check(
  'The API allows a POST with a token from the shop (preflight)',
  preflight.status >= 200 && preflight.status < 300 && originOk && (allowHeaders.includes('authorization') || allowHeaders === '*'),
  originOk
    ? `OPTIONS /api/orders answered ${preflight.status}, allow-headers "${allowHeaders}". In config/cors.php keep 'allowed_headers' => ['*'] and the api/* path.`
    : `OPTIONS /api/orders answered ${preflight.status} with Access-Control-Allow-Origin "${preflightOrigin ?? 'missing'}": fix the CORS check above first.`,
)

// 4. The shop, and the API address baked into its JavaScript.
const home = await get(`${shop}/`)
check('The shop home page answers 200', home.status === 200, home.error ? `Could not reach ${shop} (${home.error}).` : `Got status ${home.status}.`)
const scripts = [...home.text.matchAll(/<script[^>]+src="([^"]+)"/g)].map((m) => new URL(m[1], shop + '/').href)
let bundle = ''
for (const src of scripts) bundle += (await get(src)).text
check(
  'The shop was built with this API address (VITE_API_URL)',
  bundle.includes(api),
  `The shop's JavaScript does not contain "${api}". Set VITE_API_URL=${api} (no /api, no slash at the end) where the shop is built, then build or deploy again.`,
)
const endsWithApi = ['"', "'", '`'].some((quote) => bundle.includes(`${api}/api${quote}`) || bundle.includes(`${api}/api/${quote}`))
check('VITE_API_URL stops before /api (no /api/api/)', !endsWithApi && !bundle.includes('/api/api/'), 'The shop was built with an address that ends in /api, and api.js adds /api again. Set VITE_API_URL without /api.')
if (!local(shop)) {
  check('The live shop does not call your laptop', !/https?:\/\/(localhost|127\.0\.0\.1)[:/]/.test(bundle), 'The built JavaScript still contains localhost or 127.0.0.1.')
  check('Both use HTTPS', shop.startsWith('https://') && api.startsWith('https://'), 'An https:// page may not call an http:// API (the browser blocks "mixed content").')
}

console.log(`\n${passed} of ${total} checks passed.`)
process.exit(passed === total ? 0 : 1)
