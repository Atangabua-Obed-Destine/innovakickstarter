import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it, vi } from 'vitest'
import { productById } from '../test/fixtures'
import { BasketSummary } from './BasketSummary'

describe('BasketSummary', () => {
  it('says so when the basket is empty', () => {
    // TODO: render with no lines, then check the message a customer sees.
    throw new Error('TODO: write this test (README step 5)')
  })

  it('shows the total for 2 x Rice 50kg and 1 x Palm Oil 5L', () => {
    // TODO: build the two lines with productById(6) and productById(15).
    // Work out the total by hand first, then check the "Total: ..." text.
    throw new Error('TODO: write this test (README step 5)')
  })

  it('calls onRemove with the product id when Remove is clicked', async () => {
    // TODO: one line of Palm Oil 5L. Click "Remove Palm Oil 5L" and check onRemove got 15.
    throw new Error('TODO: write this test (README step 5)')
  })
})
