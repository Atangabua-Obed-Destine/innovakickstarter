// The shapes of Nkwen Traders data, as the practice API sends them.
// (These are the types you wrote in "TypeScript Essentials".)

export type OpenBranchCode = 'NKW' | 'ML4' | 'CAV' | 'BAF';
export type BranchCode = OpenBranchCode | 'LMB';
export type PaymentMethod = 'cash' | 'mtn_momo' | 'orange_money' | 'bank_transfer';
export type StockStatus = 'out' | 'low' | 'in';

export interface Product {
  id: number;
  sku: string;
  name: string;
  category: string;
  unit: string;
  price_fcfa: number; // whole FCFA, VAT included
  stock: Partial<Record<BranchCode, number>>; // Limbe has no entry yet
  reorder_level: number;
}

export interface Branch {
  code: BranchCode;
  name: string;
  city: string;
  region: string;
  manager: string;
  opened_year: number;
  status: 'open' | 'planned';
}

export interface BasketLine {
  id: number;
  qty: number;
}
export type Basket = BasketLine[];

// Answers from GET /products, /branches and /categories
export interface ProductsResponse {
  count: number;
  currency: 'FCFA';
  products: Product[];
}
export interface BranchesResponse {
  branches: Branch[];
}
export interface CategoriesResponse {
  categories: string[];
}

// POST /orders
export interface OrderRequest {
  branch: OpenBranchCode;
  customer_name: string;
  payment_method: PaymentMethod;
  items: { product_id: number; quantity: number }[];
}
export interface OrderItem {
  product_id: number;
  name: string;
  quantity: number;
  unit_price_fcfa: number;
  line_total_fcfa: number;
}
export type OrderStatus = 'pay_at_counter' | 'awaiting_payment' | 'paid' | 'delivered' | 'cancelled';
export interface Order {
  id: string;
  branch: OpenBranchCode;
  customer_name: string;
  payment_method: PaymentMethod;
  status: OrderStatus;
  items: OrderItem[];
  total_fcfa: number;
  created_at: string;
}

// Every possible answer to "place this order", as one union type.
export type OrderResult =
  | { kind: 'created'; order: Order }
  | { kind: 'invalid'; message: string; errors: Record<string, string[]> }
  | { kind: 'stock'; message: string; product_id: number; requested: number; available: number }
  | { kind: 'failed'; message: string };
