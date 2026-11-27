// Run with: npm test   (Vitest: like node --test, with expect() and fake functions)
import { afterEach, describe, expect, it, vi } from 'vitest'
import { API_URL, ApiError, apiUrl, getProducts, request } from './api'

afterEach(() => vi.unstubAllGlobals())

// Replace fetch with a fake that answers with this status and JSON.
function fakeFetch(status, body) {
  const fake = vi.fn(async () => new Response(body === null ? null : JSON.stringify(body), { status }))
  vi.stubGlobal('fetch', fake)
  return fake
}

describe('apiUrl', () => {
  it('joins the API address and the path', () => {
    expect(apiUrl('/products')).toBe(`${API_URL}/api/products`)
  })

  it('never makes a double slash or /api/api', () => {
    expect(apiUrl('products')).toBe(`${API_URL}/api/products`)
    expect(apiUrl('/api/products')).toBe(`${API_URL}/api/products`)
    expect(API_URL.endsWith('/')).toBe(false)
  })
})

describe('request', () => {
  it('returns the list of products from {data: [...]}', async () => {
    const fake = fakeFetch(200, { data: [{ id: 6, name: 'Rice 50kg' }], meta: { count: 1 } })

    const products = await getProducts()

    expect(products[0].name).toBe('Rice 50kg')
    expect(fake.mock.calls[0][0]).toBe(`${API_URL}/api/products`)
  })

  it('sends JSON and the token when asked', async () => {
    const fake = fakeFetch(201, { data: { id: 'ORD-TEST0001' } })

    await request('/orders', { method: 'POST', body: { branch: 'CAV' }, token: 'abc' })

    const options = fake.mock.calls[0][1]
    expect(options.method).toBe('POST')
    expect(options.headers.Authorization).toBe('Bearer abc')
    expect(options.headers['Content-Type']).toBe('application/json')
    expect(options.body).toBe('{"branch":"CAV"}')
  })

  it('throws an ApiError with the status and the errors for a 422', async () => {
    fakeFetch(422, { message: 'The order is not valid.', errors: { customer_name: ['The customer name field is required.'] } })

    const error = await request('/orders', { method: 'POST', body: {} }).catch((e) => e)

    expect(error).toBeInstanceOf(ApiError)
    expect(error.status).toBe(422)
    expect(error.data.errors.customer_name[0]).toMatch(/required/)
  })

  it('explains a network failure in plain words', async () => {
    vi.stubGlobal('fetch', vi.fn(async () => { throw new TypeError('Failed to fetch') }))

    await expect(getProducts()).rejects.toThrow('Could not reach the shop server')
  })
})
