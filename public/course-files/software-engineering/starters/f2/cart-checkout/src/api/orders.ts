// POST /orders on the practice API (ready for you). It checks and prices an order
// but never saves it. Every answer becomes ONE OrderResult, so the page can use
// a switch on result.kind and TypeScript checks that no case is forgotten.
import type { Order, OrderRequest, OrderResult } from '../types.ts';
import { buildUrl, type Params } from './nkwen.ts';

// The practice API wraps the order as { order, message }. (Our own Orders API in
// the Backend pathway wraps it as { data }: only this file would change.)
interface CreatedBody { order: Order; message: string }
interface InvalidBody { message: string; errors: Record<string, string[]> }
interface StockBody { message: string; product_id: number; requested: number; available: number }

export async function postOrder(order: OrderRequest, options: { idempotencyKey?: string; params?: Params } = {}): Promise<OrderResult> {
  const headers: Record<string, string> = { 'Content-Type': 'application/json', Accept: 'application/json' };
  // One key per order. The practice API and our Orders API return the first order
  // again for a repeated key (header Idempotent-Replayed: true), and answer 422 if
  // the same key arrives with a different order.
  if (options.idempotencyKey) headers['Idempotency-Key'] = options.idempotencyKey;
  let response: Response;
  try {
    response = await fetch(buildUrl('/orders', options.params), { method: 'POST', headers, body: JSON.stringify(order) });
  } catch {
    return { kind: 'failed', message: 'We could not reach Nkwen Traders. Check your connection.' };
  }
  const body: unknown = await response.json().catch(() => null);
  if (response.status === 201) return { kind: 'created', order: (body as CreatedBody).order };
  if (response.status === 422) {
    const b = body as InvalidBody;
    return { kind: 'invalid', message: b.message, errors: b.errors ?? {} };
  }
  if (response.status === 409) {
    const b = body as StockBody;
    return { kind: 'stock', message: b.message, product_id: b.product_id, requested: b.requested, available: b.available };
  }
  return { kind: 'failed', message: `Sorry, our server had a problem (${response.status}).` };
}
