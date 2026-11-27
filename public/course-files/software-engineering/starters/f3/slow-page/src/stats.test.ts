import { describe, expect, it } from 'vitest'
import { makeSales, uniqueCustomers } from './stats'

describe('uniqueCustomers', () => {
  it('counts each phone number once', () => {
    const sales = [
      { phone: '677000001', productId: 6, quantity: 1 },
      { phone: '699000002', productId: 9, quantity: 2 },
      { phone: '677000001', productId: 15, quantity: 1 },
    ]
    expect(uniqueCustomers(sales)).toBe(2)
  })

  it('counts a month of 20,000 sales in under 100 ms', () => {
    const sales = makeSales(20000)

    const start = performance.now()
    const count = uniqueCustomers(sales)
    const ms = Math.round(performance.now() - start)

    expect(count).toBe(12650)
    // On a cheap phone, JavaScript runs about 4 times slower than on your laptop.
    expect(ms, `uniqueCustomers took ${ms} ms for 20,000 sales`).toBeLessThan(100)
  })
})
