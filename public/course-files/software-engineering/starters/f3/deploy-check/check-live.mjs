// Checks a deployed frontend the way a customer's phone would meet it.
// Usage:  node check-live.mjs https://your-shop.netlify.app products/6
//         (the second part is any deep link in your app: a page address that is not the home page)
// Needs Node.js 18 or newer. Works for any frontend: React, Vue, Angular, Next.js.
const base = (process.argv[2] || '').replace(/\/+$/, '')
// Git Bash on Windows turns "/products/6" into "C:/Program Files/Git/products/6", so we accept "products/6" too.
const deepLink = '/' + (process.argv[3] || 'products/6').replace(/^[A-Za-z]:\/.*?\/Git\//, '').replace(/^\/+/, '')
if (!base.startsWith('http')) {
  console.log('Give the address of your site, for example: node check-live.mjs https://nkwen-shop.netlify.app products/6')
  process.exit(1)
}

let passed = 0
let total = 0
function check(name, ok, hint) {
  total++
  if (ok) passed++
  console.log(`${ok ? 'PASS' : 'FAIL'}  ${name}${ok ? '' : `\n      - ${hint}`}`)
}

async function get(path) {
  try {
    const response = await fetch(base + path, { headers: { 'Accept-Encoding': 'gzip, br' }, signal: AbortSignal.timeout(30000) })
    return { status: response.status, headers: response.headers, text: await response.text() }
  } catch (error) {
    return { status: 0, headers: new Headers(), text: '', error: error.message }
  }
}

const home = await get('/')
check('The home page answers 200', home.status === 200, home.error ? `Could not reach ${base} (${home.error}).` : `Got status ${home.status}.`)
check('The site uses HTTPS', base.startsWith('https://') || base.includes('localhost') || base.includes('127.0.0.1'), 'Use the https:// address. Free hosts give you one.')
check('The page says its language (<html lang>)', /<html[^>]*\slang="[a-z]{2}/i.test(home.text), 'Add lang="en" (or lang="fr") to the <html> tag in index.html.')
check('The page has a mobile viewport tag', /<meta[^>]+name="viewport"[^>]+width=device-width/i.test(home.text), 'Add <meta name="viewport" content="width=device-width, initial-scale=1"> to index.html.')

const deep = await get(deepLink)
check(
  `A deep link (${deepLink}) opens the app, not a 404`,
  deep.status === 200 && deep.text.includes('<div id="root"') === home.text.includes('<div id="root"'),
  `Got status ${deep.status}. A single-page app needs a "rewrite": every unknown address returns index.html. See netlify.toml, public/_redirects or vercel.json in the starter.`,
)

// Find the JavaScript files the home page loads, and look inside them.
const scripts = [...home.text.matchAll(/<script[^>]+src="([^"]+)"/g)].map((m) => m[1]).filter((src) => !src.startsWith('http'))
let bundle = ''
let compressed = false
for (const src of scripts) {
  const file = await get(src.startsWith('/') ? src : '/' + src)
  bundle += file.text
  if (/gzip|br|zstd/.test(file.headers.get('content-encoding') || '')) compressed = true
}
check('The JavaScript was found', scripts.length > 0 && bundle.length > 0, 'No <script src> found in the home page. Is this the built site (npm run build)?')
check(
  'The JavaScript does not point to your laptop (localhost)',
  !/https?:\/\/(localhost|127\.0\.0\.1)[:/]/.test(bundle),
  'The built code still contains http://localhost or 127.0.0.1. Set the environment variable (for example VITE_API_URL) on the host, then deploy again.',
)
check('The JavaScript is sent compressed (gzip or brotli)', compressed, 'Netlify, Vercel and Cloudflare Pages compress for you. On another host, switch compression on.')

console.log(`\n${passed} of ${total} checks passed.`)
process.exit(passed === total ? 0 : 1)
