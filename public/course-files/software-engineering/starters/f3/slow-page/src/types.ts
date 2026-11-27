// The shape of one product from the Nkwen course API (GET /products).
export type BranchCode = 'NKW' | 'ML4' | 'CAV' | 'BAF'

export type Product = {
  id: number
  sku: string
  name: string
  category: string
  unit: string
  price_fcfa: number
  stock: Record<BranchCode, number>
}

// One line in the basket: which product, and how many.
export type BasketLine = { product: Product; quantity: number }
