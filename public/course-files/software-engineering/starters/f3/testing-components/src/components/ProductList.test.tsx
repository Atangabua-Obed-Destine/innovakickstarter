import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { products } from '../test/fixtures'
import { mockFetch, mockFetchSequence } from '../test/mockFetch'
import { ProductList } from './ProductList'

describe('ProductList', () => {
  // Worked example: the API is replaced by mockFetch, so no network is used.
  it('asks the API for /products', async () => {
    const fake = mockFetch(200, { count: products.length, currency: 'FCFA', products })

    render(<ProductList branch="CAV" onAdd={() => {}} />)

    // findBy... waits (up to 1 second) for something that appears later.
    expect(await screen.findByRole('heading', { name: 'Products' })).toBeInTheDocument()
    expect(fake).toHaveBeenCalledTimes(1)
    expect(String(fake.mock.calls[0][0])).toMatch(/\/products$/)
  })

  it('shows a loading message, then every product', async () => {
    // TODO: mockFetch with all products. Check "Loading products…" is shown first (getByRole('status')),
    // then wait for the count text, and check there is one card (role "article") per product.
    throw new Error('TODO: write this test (README step 6)')
  })

  it('shows an error when the API fails, and "Try again" loads the products', async () => {
    // TODO: mockFetchSequence: first a 503 with {message: 'Service unavailable'}, then 200 with the products.
    // Check the alert appears, click "Try again", then wait for a product heading.
    throw new Error('TODO: write this test (README step 7)')
  })

  it('finds products whatever the letter case: RICE finds both rice bags', async () => {
    // TODO (test first!): type RICE into the search box with userEvent,
    // then check the count text and that both rice headings are shown.
    throw new Error('TODO: write this test (README step 8)')
  })
})
