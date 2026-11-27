// This week's offers: which products, the old price, and the photo file.
export type Offer = { productId: number; wasFcfa: number; photo: string }

export const OFFERS: Offer[] = [
  { productId: 6, wasFcfa: 38500, photo: 'rice-50kg' },
  { productId: 15, wasFcfa: 7200, photo: 'palm-oil-5l' },
  { productId: 9, wasFcfa: 850, photo: 'sugar-1kg' },
  { productId: 3, wasFcfa: 1050, photo: 'beans-red' },
  { productId: 16, wasFcfa: 7900, photo: 'vegetable-oil-5l' },
  { productId: 2, wasFcfa: 3100, photo: 'milk-powder-400g' },
  { productId: 13, wasFcfa: 450, photo: 'soap-bar' },
  { productId: 19, wasFcfa: 2900, photo: 'plantain-bunch' },
]

// The offers end on Sunday 29 November 2026 (months count from 0, so 10 is November).
export const OFFERS_END = new Date(2026, 10, 29)
