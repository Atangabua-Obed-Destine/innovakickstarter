// Consumer contract test for the web shop (Vitest).
// Copy this file to web/tests/orders.contract.test.ts (a tests/ folder next to src/).
//
// It feeds YOUR code the agreed example answers from contract/examples/. If someone changes
// the contract (and its examples) without updating the shop, this test fails in CI,
// before a customer meets a broken checkout.
//
// It expects a function placeOrder(request, idempotencyKey) that returns either
//   { ok: true, order }  or  { ok: false, status, message, fieldErrors }.
// If your code is shaped differently, change the import and the expectations to match it,
// but keep what each test checks.
import { afterEach, describe, expect, it, vi } from 'vitest';
import created from '../../contract/examples/order-created.json';
import request from '../../contract/examples/order-request.json';
import conflict from '../../contract/examples/error-409.json';
import invalid from '../../contract/examples/error-422.json';
import { placeOrder } from '../src/api/orders'; // TODO: the file and function that send POST /api/orders

// Make fetch answer like the API would, without any network.
function serverAnswers(status: number, body: unknown) {
  const fetchMock = vi.fn().mockResolvedValue(
    new Response(JSON.stringify(body), { status, headers: { 'Content-Type': 'application/json' } }),
  );
  vi.stubGlobal('fetch', fetchMock);
  return fetchMock;
}

afterEach(() => {
  vi.unstubAllGlobals();
});

describe('POST /api/orders, as the web shop uses it', () => {
  it('sends ids and quantities (never prices) with an Idempotency-Key', async () => {
    const fetchMock = serverAnswers(201, created);
    await placeOrder(request, 'key-123');
    const [url, init] = fetchMock.mock.calls[0];
    expect(String(url)).toMatch(/\/api\/orders$/);
    expect(init.method).toBe('POST');
    expect(new Headers(init.headers).get('Idempotency-Key')).toBe('key-123');
    expect(JSON.stringify(JSON.parse(init.body).items)).not.toMatch(/price/);
  });

  it('reads the order number and the total from the server', async () => {
    serverAnswers(201, created);
    const result = await placeOrder(request, 'key-123');
    expect(result).toMatchObject({ ok: true, order: { id: created.data.id, total_fcfa: created.data.total_fcfa } });
  });

  it('turns a 409 into a message that says how many are left', async () => {
    serverAnswers(409, conflict);
    const result = await placeOrder(request, 'key-123');
    expect(result.ok).toBe(false);
    if (!result.ok) expect(result.message).toContain(String(conflict.available));
  });

  it('keeps the 422 field errors so the form can show each one next to its field', async () => {
    serverAnswers(422, invalid);
    const result = await placeOrder(request, 'key-123');
    expect(result).toMatchObject({ ok: false, status: 422, fieldErrors: invalid.errors });
  });
});
