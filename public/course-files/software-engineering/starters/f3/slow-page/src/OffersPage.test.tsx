import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it, vi } from 'vitest'
import products from '../public/data/products.json'
import { OffersPage } from './OffersPage'

function serveProducts() {
  vi.stubGlobal('fetch', async (url: string) => {
    if (String(url).endsWith('/data/products.json')) return Response.json(products)
    const id = Number(String(url).match(/(\d+)\.json$/)?.[1])
    return Response.json(products.products.find((p) => p.id === id))
  })
}

// A slow page makes slow tests, so these tests may take up to a minute each.
const SLOW = { timeout: 60000 }

describe('OffersPage', () => {
  it('gives every image a width and height, so nothing jumps while photos load', async () => {
    serveProducts()
    render(<OffersPage />)
    await screen.findByRole('heading', { name: 'Plantain (bunch)' }, SLOW)

    for (const img of document.querySelectorAll('img')) {
      expect(img.getAttribute('width'), `${img.getAttribute('src')} has no width`).toBeTruthy()
      expect(img.getAttribute('height'), `${img.getAttribute('src')} has no height`).toBeTruthy()
    }
  }, SLOW.timeout)

  it('loads offer photos lazily, but never the big photo at the top', async () => {
    serveProducts()
    render(<OffersPage />)
    await screen.findByRole('heading', { name: 'Plantain (bunch)' }, SLOW)

    const [hero, ...offerPhotos] = document.querySelectorAll('img')
    expect(hero.getAttribute('loading'), 'the hero photo must not be lazy').not.toBe('lazy')
    expect(offerPhotos).toHaveLength(8)
    for (const img of offerPhotos) {
      expect(img.getAttribute('loading'), `${img.getAttribute('src')} should be lazy`).toBe('lazy')
    }
  }, SLOW.timeout)

  it('still shows the price history when head office asks for it', async () => {
    serveProducts()
    const user = userEvent.setup()
    render(<OffersPage />)

    await user.click(screen.getByRole('button', { name: /show price history/i }))
    expect(await screen.findByText('Rice 50kg: shelf price, last 12 months', {}, SLOW)).toBeInTheDocument()
  }, SLOW.timeout)
})
