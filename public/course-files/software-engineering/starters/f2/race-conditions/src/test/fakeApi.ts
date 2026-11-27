// A pretend Nkwen practice API for the tests: offline, and always the same answers.
// It answers like the real one (filters, 404, 422, 409, ?fail=) from the same data.
import { vi } from 'vitest';
import { NKWEN } from '../data/nkwen.ts';

export interface FakeCall {
  method: string;
  path: string;
  body: unknown;
  headers: Record<string, string>;
}

export interface FakeApi {
  calls: FakeCall[];
  delayFor: (path: string) => number; // milliseconds before an answer
  failWhen: (path: string) => boolean; // answer 500 when true
}

type Answer = { ok: boolean; status: number; statusText: string; json: () => Promise<unknown> };
const answer = (status: number, body: unknown): Answer => ({ ok: status < 400, status, statusText: String(status), json: async () => body });

function products(params: URLSearchParams) {
  let list = [...NKWEN.products];
  const category = params.get('category');
  const search = params.get('search');
  const branch = params.get('branch');
  if (category) list = list.filter((p) => p.category.toLowerCase() === category.toLowerCase());
  if (search) list = list.filter((p) => p.name.toLowerCase().includes(search.trim().toLowerCase()));
  if (branch) {
    const code = branch.toUpperCase();
    if (!NKWEN.branches.some((b) => b.code === code)) return answer(404, { message: `Unknown branch code "${code}". See /branches.` });
    list = list.filter((p) => (p.stock[code as keyof typeof p.stock] ?? 0) > 0);
  }
  const sort = params.get('sort');
  if (sort === 'price') list.sort((a, b) => a.price_fcfa - b.price_fcfa);
  if (sort === '-price') list.sort((a, b) => b.price_fcfa - a.price_fcfa);
  if (sort === 'name') list.sort((a, b) => a.name.localeCompare(b.name));
  return answer(200, { count: list.length, currency: 'FCFA', products: list });
}

let orderNumber = 0;
function order(body: unknown) {
  const b = (body ?? {}) as Record<string, unknown>;
  const errors: Record<string, string[]> = {};
  const open = NKWEN.branches.filter((x) => x.status === 'open').map((x) => x.code);
  if (!b.branch) errors.branch = ['The branch field is required.'];
  else if (!open.includes(b.branch as never)) errors.branch = [`The branch must be one of the open branches: ${open.join(', ')}.`];
  const name = typeof b.customer_name === 'string' ? b.customer_name : '';
  if (!name) errors.customer_name = ['The customer name field is required.'];
  else if (name.length < 2) errors.customer_name = ['The customer name field must be at least 2 characters.'];
  else if (name.length > 80) errors.customer_name = ['The customer name field must not be greater than 80 characters.'];
  if (!b.payment_method) errors.payment_method = ['The payment method field is required.'];
  else if (!NKWEN.payment_methods.includes(b.payment_method as never)) errors.payment_method = ['The selected payment method is invalid.'];
  const items = Array.isArray(b.items) ? (b.items as Record<string, unknown>[]) : [];
  if (items.length === 0) errors.items = ['The items field is required.'];
  items.forEach((item, i) => {
    const q = item.quantity;
    if (typeof q !== 'number' || !Number.isInteger(q)) errors[`items.${i}.quantity`] = [`The items.${i}.quantity field must be an integer.`];
    else if (q < 1) errors[`items.${i}.quantity`] = [`The items.${i}.quantity field must be at least 1.`];
  });
  if (Object.keys(errors).length) return answer(422, { message: 'The order is not valid.', errors });
  const lines = [];
  for (const item of items) {
    const p = NKWEN.products.find((x) => x.id === item.product_id);
    if (!p) return answer(422, { message: 'The order is not valid.', errors: { 'items.0.product_id': [`No product with id ${item.product_id}.`] } });
    const available = p.stock[b.branch as keyof typeof p.stock] ?? 0;
    const quantity = item.quantity as number;
    if (quantity > available) return answer(409, { message: `Not enough ${p.name} at this branch.`, product_id: p.id, requested: quantity, available });
    lines.push({ product_id: p.id, name: p.name, quantity, unit_price_fcfa: p.price_fcfa, line_total_fcfa: p.price_fcfa * quantity });
  }
  orderNumber += 1;
  return answer(201, {
    order: {
      id: `ORD-TEST${String(orderNumber).padStart(4, '0')}`,
      branch: b.branch,
      customer_name: name,
      items: lines,
      total_fcfa: lines.reduce((sum, l) => sum + l.line_total_fcfa, 0),
      payment_method: b.payment_method,
      status: b.payment_method === 'cash' ? 'pay_at_counter' : 'awaiting_payment',
      created_at: '2026-12-03T10:15:00+01:00',
      saved: false,
    },
    message: 'Order checked and priced. This is a practice API: nothing was saved.',
  });
}

export function installFakeApi(): FakeApi {
  const api: FakeApi = { calls: [], delayFor: () => 5, failWhen: () => false };
  vi.stubGlobal(
    'fetch',
    vi.fn(async (input: string | URL, init?: RequestInit) => {
      const url = new URL(String(input));
      const path = url.pathname.replace(/^.*\/api\/course\/nkwen/, '') + url.search;
      const method = (init?.method ?? 'GET').toUpperCase();
      const body = init?.body ? JSON.parse(String(init.body)) : null;
      const headers = Object.fromEntries(new Headers(init?.headers).entries());
      api.calls.push({ method, path, body, headers });
      await new Promise((resolve) => setTimeout(resolve, api.delayFor(path)));
      init?.signal?.throwIfAborted();
      if (api.failWhen(path) || url.searchParams.get('fail')) return answer(500, { message: 'Server error (practice error from ?fail=500).' });
      const route = url.pathname.replace(/^.*\/api\/course\/nkwen/, '');
      if (method === 'POST' && route === '/orders') return order(body);
      if (route === '/products') return products(url.searchParams);
      const one = route.match(/^\/products\/(\d+)$/);
      if (one) {
        const p = NKWEN.products.find((x) => x.id === Number(one[1]));
        return p ? answer(200, p) : answer(404, { message: `No product with id ${one[1]}.` });
      }
      if (route === '/branches') return answer(200, { branches: NKWEN.branches });
      if (route === '/categories') return answer(200, { categories: NKWEN.categories });
      return answer(404, { message: 'Not found.' });
    }),
  );
  return api;
}
