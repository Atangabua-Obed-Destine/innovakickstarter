// Reads products from the Nkwen course API. This file runs on the server only.
import { cacheLife } from 'next/cache'

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

const API_URL = process.env.NKWEN_API_URL ?? 'https://innovakickstarter.com/api/course/nkwen'

// All 20 products. 'use cache' keeps the answer for hours, so most visitors never wait for the API.
export async function getProducts(): Promise<Product[]> {
  'use cache'
  cacheLife('hours')
  const response = await fetch(`${API_URL}/products`)
  if (!response.ok) throw new Error(`The API answered ${response.status}`)
  const data: { products: Product[] } = await response.json()
  return data.products
}

// One product, or null when there is no product with that id.
export async function getProduct(id: number): Promise<Product | null> {
  const products = await getProducts()
  return products.find((product) => product.id === id) ?? null
}

// 36005 -> "36,005 FCFA" (the same helper as format.js in Chapter 4)
export function formatFcfa(amount: number): string {
  return amount.toLocaleString('en-US') + ' FCFA'
}
