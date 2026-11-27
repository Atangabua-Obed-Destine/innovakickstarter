// Worked examples: plain functions are the easiest things to test.
// Run them with: npm test
import { describe, expect, it } from 'vitest'
import { formatFcfa, stockStatus, stockText } from './format'

describe('formatFcfa', () => {
  it('adds thousands separators and the currency', () => {
    expect(formatFcfa(36005)).toBe('36,005 FCFA')
  })
})

describe('stockStatus and stockText', () => {
  it('follows the shop badge rule at the edges: 0, 1, 5 and 6', () => {
    expect(stockStatus(0)).toBe('out')
    expect(stockStatus(1)).toBe('low')
    expect(stockStatus(5)).toBe('low')
    expect(stockStatus(6)).toBe('in')
  })

  it('gives the customer readable words', () => {
    expect(stockText(0)).toBe('Out of stock')
    expect(stockText(3)).toBe('Only 3 left')
  })
})
