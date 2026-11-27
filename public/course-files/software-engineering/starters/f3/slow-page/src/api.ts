import { OFFERS } from './offers'
import type { Offer } from './offers'
import type { Product } from './types'

export type OfferWithProduct = Offer & { product: Product }

// Loads the product details for every offer, in the order of OFFERS.
export async function loadOffers(): Promise<OfferWithProduct[]> {
  const result: OfferWithProduct[] = []
  for (const offer of OFFERS) {
    const response = await fetch(`/data/products/${offer.productId}.json`)
    if (!response.ok) throw new Error(`Could not load product ${offer.productId}`)
    const product: Product = await response.json()
    result.push({ ...offer, product })
  }
  return result
}
