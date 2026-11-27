// Checks the built site (the dist folder) against Nkwen Traders' performance budget.
// A budget is a limit the team agrees on, like a household budget, but for bytes.
// Run it after a build:  npm run build && npm run budget
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs'
import { join } from 'node:path'
import { gzipSync } from 'node:zlib'

const BUDGET = {
  entryJsKb: 100, // the JavaScript every visitor downloads first (gzip, as servers send it)
  imageKb: 100, // any one image
  allImagesKb: 400, // all images together
}

const dist = process.argv[2] || 'dist'
if (!existsSync(join(dist, 'index.html'))) {
  console.log(`No ${dist}/index.html. Run "npm run build" first.`)
  process.exit(1)
}

const kb = (bytes) => Math.round(bytes / 102.4) / 10
const gzipKb = (file) => kb(gzipSync(readFileSync(file)).length)
const problems = []
const rows = []

// 1. The entry script: the one <script type="module"> in index.html.
const html = readFileSync(join(dist, 'index.html'), 'utf8')
const entry = html.match(/<script[^>]+type="module"[^>]+src="\/?([^"]+)"/)
if (entry) {
  const size = gzipKb(join(dist, entry[1]))
  rows.push(['Entry JavaScript (gzip)', size, BUDGET.entryJsKb])
  if (size > BUDGET.entryJsKb) problems.push(`The entry JavaScript is ${size} KB; the budget is ${BUDGET.entryJsKb} KB. Look for big libraries you could remove or load later.`)
}

// 2. Other JavaScript files: loaded later, only when needed (lazy chunks).
const assets = existsSync(join(dist, 'assets')) ? readdirSync(join(dist, 'assets')) : []
for (const name of assets.filter((n) => n.endsWith('.js') && !(entry && entry[1].endsWith(n)))) {
  rows.push([`Lazy chunk ${name} (gzip)`, gzipKb(join(dist, 'assets', name)), '-'])
}

// 3. Images, anywhere in dist.
const images = []
const walk = (dir) => {
  for (const name of readdirSync(dir)) {
    const path = join(dir, name)
    if (statSync(path).isDirectory()) walk(path)
    else if (/\.(png|jpe?g|webp|avif|gif|svg)$/i.test(name)) images.push(path)
  }
}
walk(dist)
let allImages = 0
for (const path of images) {
  const size = kb(statSync(path).size)
  allImages += size
  if (size > BUDGET.imageKb) problems.push(`${path} is ${size} KB; the budget for one image is ${BUDGET.imageKb} KB.`)
}
rows.push([`All ${images.length} images`, Math.round(allImages * 10) / 10, BUDGET.allImagesKb])
if (allImages > BUDGET.allImagesKb) problems.push(`All images together are ${Math.round(allImages)} KB; the budget is ${BUDGET.allImagesKb} KB.`)

console.log('What                                   KB   Budget')
for (const [what, size, budget] of rows) console.log(`${what.padEnd(36)} ${String(size).padStart(6)}   ${budget}`)
console.log('')
if (problems.length) {
  for (const p of problems) console.log('OVER BUDGET: ' + p)
  process.exit(1)
}
console.log('Within budget.')
