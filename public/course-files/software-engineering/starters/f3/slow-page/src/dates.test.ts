import { describe, expect, it } from 'vitest'
import { offerEndText } from './dates'
import { OFFERS_END } from './offers'

// This test already passes. Keep it green: a speed fix must not change what customers read.
describe('offerEndText', () => {
  it('says when the offers end, in English and French', () => {
    expect(offerEndText(OFFERS_END)).toBe('Offers end Sunday 29 November · Fin des offres : dimanche 29 novembre')
  })
})
