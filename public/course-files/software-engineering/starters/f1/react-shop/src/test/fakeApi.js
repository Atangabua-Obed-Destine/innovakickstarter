// A pretend Nkwen API for the tests, so they run offline and always give the
// same answers. It answers like the real one, from the same data (nkwen.js).
import { vi } from 'vitest';
import { NKWEN } from '../data/nkwen.js';

export function installFakeApi() {
  const api = { failing: false, calls: [] };
  const answer = (status, body) => ({ ok: status < 400, status, statusText: String(status), json: async () => body });
  vi.stubGlobal(
    'fetch',
    vi.fn(async (input) => {
      const url = new URL(String(input));
      api.calls.push(url.pathname + url.search);
      await new Promise((resolve) => setTimeout(resolve, 5));
      if (api.failing || url.searchParams.get('fail')) return answer(500, { message: 'Server error (practice).' });
      if (url.pathname.endsWith('/products')) return answer(200, { count: NKWEN.products.length, currency: 'FCFA', products: NKWEN.products });
      if (url.pathname.endsWith('/branches')) return answer(200, { branches: NKWEN.branches });
      if (url.pathname.endsWith('/categories')) return answer(200, { categories: NKWEN.categories });
      return answer(404, { message: 'Not found.' });
    }),
  );
  return api;
}
