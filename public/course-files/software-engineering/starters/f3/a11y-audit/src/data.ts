import type { Product } from './types'

// The products this page needs, from nkwen.json, with a photo for each.
export const rice: Product = { id: 6, sku: 'NKT-GRA-006', name: 'Rice 50kg', category: 'Grains', unit: 'bag', price_fcfa: 36005, stock: { NKW: 42, ML4: 3, CAV: 58, BAF: 21 } }
export const palmOil: Product = { id: 15, sku: 'NKT-OIL-015', name: 'Palm Oil 5L', category: 'Oils', unit: 'jerrycan', price_fcfa: 6600, stock: { NKW: 15, ML4: 68, CAV: 31, BAF: 84 } }
export const sugar: Product = { id: 9, sku: 'NKT-GRO-009', name: 'Sugar 1kg', category: 'Groceries', unit: 'bag', price_fcfa: 760, stock: { NKW: 63, ML4: 26, CAV: 0, BAF: 42 } }
export const matches: Product = { id: 12, sku: 'NKT-HOU-012', name: 'Matches (box)', category: 'Household', unit: 'box', price_fcfa: 100, stock: { NKW: 84, ML4: 47, CAV: 3, BAF: 63 } }
export const soap: Product = { id: 13, sku: 'NKT-HOU-013', name: 'Soap (bar)', category: 'Household', unit: 'bar', price_fcfa: 410, stock: { NKW: 31, ML4: 3, CAV: 47, BAF: 10 } }

export const photos: Record<number, string> = {
  6: '/photos/rice.svg',
  15: '/photos/palm-oil.svg',
  9: '/photos/sugar.svg',
  12: '/photos/matches.svg',
  13: '/photos/soap.svg',
}
