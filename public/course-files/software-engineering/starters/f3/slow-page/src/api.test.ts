import { describe, expect, it, vi } from 'vitest'
import products from '../public/data/products.json'
import { loadOffers } from './api'

// A pretend server that answers like the files in public/data.
function fakeServer() {
  const fake = vi.fn(async (url: string | URL | Request) => {
    const path = String(url)
    if (path.endsWith('/data/products.json')) return Response.json(products)
    const one = path.match(/\/data\/products\/(\d+)\.json$/)
    const product = one && products.products.find((p) => p.id === Number(one[1]))
    return product ? Response.json(product) : new Response('Not found', { status: 404 })
  })
  vi.stubGlobal('fetch', fake)
  return fake
}

describe('loadOffers', () => {
  it('returns the 8 offers, in order, with their products', async () => {
    fakeServer()
    const offers = await loadOffers()

    expect(offers).toHaveLength(8)
    expect(offers[0].product.name).toBe('Rice 50kg')
    expect(offers[7].product.name).toBe('Plantain (bunch)')
  })

  it('needs at most 2 requests, because every request on 3G waits for the network', async () => {
    const fake = fakeServer()
    await loadOffers()

    expect(fake.mock.calls.length, `loadOffers made ${fake.mock.calls.length} requests`).toBeLessThanOrEqual(2)
  })
})
