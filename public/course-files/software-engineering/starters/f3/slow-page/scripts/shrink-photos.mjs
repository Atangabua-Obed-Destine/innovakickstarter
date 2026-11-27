// Turns every big PNG in public/photos into a small WebP photo, then deletes the PNG.
// WebP is a photo format made for the web: much smaller files that look the same.
// 1. Install the image tool once:  npm install --save-dev sharp
// 2. Run:                          npm run shrink
import { readdirSync, unlinkSync } from 'node:fs'
import sharp from 'sharp'

const folder = 'public/photos'

for (const name of readdirSync(folder).filter((n) => n.endsWith('.png'))) {
  const input = `${folder}/${name}`
  const output = input.replace(/\.png$/, '.webp')

  // How wide is the photo on the page? Look at the CSS and at DevTools.
  // Make it about twice that, for sharp phone screens, and no bigger.
  const width = 0 // TODO: one width for hero.png, another for the offer photos

  // TODO: use sharp to resize the photo to `width` and save it as WebP (quality about 70) at `output`.
  // Read the sharp docs: https://sharp.pixelplumbing.com/api-resize and https://sharp.pixelplumbing.com/api-output#webp
  throw new Error(`TODO: shrink ${input} to ${width}px and save ${output}`)

  unlinkSync(input)
  console.log(`${output} done`)
}
