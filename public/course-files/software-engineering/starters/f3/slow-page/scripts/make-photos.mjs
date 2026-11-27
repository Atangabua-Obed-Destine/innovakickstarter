// Makes the product photos for this page, on your computer, so you do not download them.
// They are big on purpose: like photos taken on a phone and uploaded as they are.
// Run it once after npm install:  npm run photos
import { mkdirSync, writeFileSync } from 'node:fs'
import { deflateSync } from 'node:zlib'

// A tiny PNG writer (PNG = a picture file format that never loses detail, so it is often large).
function crc32(bytes) {
  let crc = -1
  for (const byte of bytes) {
    crc ^= byte
    for (let k = 0; k < 8; k++) crc = (crc >>> 1) ^ (0xedb88320 & -(crc & 1))
  }
  return (crc ^ -1) >>> 0
}
function chunk(type, data) {
  const out = Buffer.alloc(12 + data.length)
  out.writeUInt32BE(data.length, 0)
  out.write(type, 4, 'ascii')
  data.copy(out, 8)
  out.writeUInt32BE(crc32(out.subarray(4, 8 + data.length)), 8 + data.length)
  return out
}
function png(width, height, pixel) {
  const raw = Buffer.alloc((width * 3 + 1) * height)
  let i = 0
  for (let y = 0; y < height; y++) {
    raw[i++] = 0
    for (let x = 0; x < width; x++) {
      const [r, g, b] = pixel(x, y)
      raw[i++] = r
      raw[i++] = g
      raw[i++] = b
    }
  }
  const header = Buffer.alloc(13)
  header.writeUInt32BE(width, 0)
  header.writeUInt32BE(height, 4)
  header[8] = 8 // 8 bits per colour
  header[9] = 2 // red, green, blue
  const signature = Buffer.from([137, 80, 78, 71, 13, 10, 26, 10])
  return Buffer.concat([signature, chunk('IHDR', header), chunk('IDAT', deflateSync(raw)), chunk('IEND', Buffer.alloc(0))])
}

// The same "random" numbers on every computer, so everyone gets the same files.
let seed = 2026
const random = () => (seed = (seed * 16807) % 2147483647) / 2147483647
const clamp = (v) => Math.max(0, Math.min(255, Math.round(v)))

// A soft colour gradient with a little grain, like a real photo.
function photo(width, height, [r, g, b]) {
  return png(width, height, (x, y) => {
    const shade = 0.75 + 0.25 * Math.sin((x + y) / (width / 3))
    const grain = (random() - 0.5) * 6
    return [clamp(r * shade + grain), clamp(g * shade + grain), clamp(b * shade + grain)]
  })
}

const colours = {
  'rice-50kg': [222, 205, 170], 'palm-oil-5l': [214, 96, 40], 'sugar-1kg': [236, 236, 240], 'beans-red': [150, 50, 45],
  'vegetable-oil-5l': [230, 200, 80], 'milk-powder-400g': [70, 120, 200], 'soap-bar': [90, 170, 210], 'plantain-bunch': [120, 170, 60],
}

mkdirSync('public/photos', { recursive: true })
writeFileSync('public/photos/hero.png', photo(1400, 600, [190, 90, 40]))
for (const [name, colour] of Object.entries(colours)) {
  writeFileSync(`public/photos/${name}.png`, photo(1000, 750, colour))
}
console.log('Made 9 photos in public/photos/')
