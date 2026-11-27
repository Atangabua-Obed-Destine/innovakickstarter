import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it, vi } from 'vitest'
import { productById } from '../test/fixtures'
import { ProductCard } from './ProductCard'

const rice = productById(6) // Rice 50kg
const bread = productById(1) // Bread (loaf)

describe('ProductCard', () => {
  // Worked example: Arrange (render), Act (nothing to do here), Assert (expect).
  it('shows the product name as a heading', () => {
    render(<ProductCard product={rice} branch="NKW" onAdd={() => {}} />)

    expect(screen.getByRole('heading', { name: 'Rice 50kg' })).toBeInTheDocument()
  })

  it('shows the price in FCFA', () => {
    // TODO: render Rice 50kg, then check the formatted price is on the screen.
    // Hint: screen.getByText('...') with the exact text a customer reads.
    throw new Error('TODO: write this test (README step 3)')
  })

  it('shows "Only N left" when stock is low at the branch', () => {
    // TODO: find a branch in fixtures.ts where Rice 50kg has 1 to 5 bags, and check the badge text.
    throw new Error('TODO: write this test (README step 3)')
  })

  it('disables the Add button when the branch has none left', () => {
    // TODO: use a branch where Bread (loaf) has 0. Find the button by its role and name,
    // then check it is disabled AND that "Out of stock" is shown.
    throw new Error('TODO: write this test (README step 4)')
  })

  it('calls onAdd with the product when the customer clicks Add', async () => {
    // TODO: make a fake function with vi.fn(), pass it as onAdd,
    // click the button with userEvent, then check the fake was called once, with rice.
    throw new Error('TODO: write this test (README step 4)')
  })
})
