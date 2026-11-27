// Contract tests: does an API keep the promises in contract/README.md?
// They send real HTTP requests and compare each answer with contract/examples.
//
// Against the stand-in API (the default; it starts by itself):
//     node --test "contract/*.test.mjs"
// Against your real API (start it first):
//     API_URL=http://127.0.0.1:8000 node --test "contract/*.test.mjs"
//     PowerShell: $env:API_URL="http://127.0.0.1:8000"; node --test "contract/*.test.mjs"
// The tests create a few orders named "Contract Test" and cancel them at the end.
import { test, before, after } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { randomUUID } from 'node:crypto';
import { shapeProblems } from './shape.mjs';
import { createStandIn, loadData } from './stand-in-api.mjs';

const example = (name) => JSON.parse(readFileSync(new URL(`./examples/${name}.json`, import.meta.url), 'utf8'));
// Demo staff accounts from the Chapter 9 Staff API (StaffSeeder). Public training passwords only.
const MANAGER = example('login-request'); // manager.nkw@nkwen.test: branch manager at Nkwen (NKW)
const HEAD_OFFICE = { email: 'headoffice@nkwen.test', password: MANAGER.password };
const CASHIER = { email: 'cashier.nkw@nkwen.test', password: MANAGER.password };
let base = process.env.API_URL?.replace(/\/+$/, '');
let server;
const created = []; // orders to cancel at the end

before(async () => {
  if (!base) {
    server = createStandIn(await loadData(process.env.STANDIN_DATA_URL || 'local'));
    await new Promise((resolve) => server.listen(0, '127.0.0.1', resolve));
    base = `http://127.0.0.1:${server.address().port}`;
  }
});

after(async () => {
  // Tidy up: cancel the test orders so their stock goes back. Errors are ignored here.
  try {
    const token = await login(HEAD_OFFICE); // head office may cancel at every branch
    for (const id of created) await call('PATCH', `/api/orders/${id}`, { body: { status: 'cancelled' }, token });
  } catch { /* login may not exist yet in Sprint 1 */ }
  server?.close();
});

async function call(method, path, { body, token, headers = {} } = {}) {
  const res = await fetch(base + path, {
    method,
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', ...(token ? { Authorization: `Bearer ${token}` } : {}), ...headers },
    body: body === undefined ? undefined : JSON.stringify(body),
  });
  const text = await res.text();
  let json = null;
  if (text.trim()) {
    try { json = JSON.parse(text); } catch { assert.fail(`${method} ${path} did not answer JSON. It starts with: ${text.slice(0, 80)}`); }
  }
  return { status: res.status, headers: res.headers, json };
}

function assertShape(actual, name) {
  let expected = example(name);
  // In a 422, "errors" maps each bad field to its messages, so its keys depend on the request.
  if (name === 'error-422') expected = { message: expected.message, errors: {} };
  const problems = shapeProblems(expected, actual);
  assert.deepEqual(problems, [], `The answer does not match contract/examples/${name}.json:\n  ${problems.join('\n  ')}`);
}

const newOrder = (overrides = {}) => ({
  branch: 'CAV', customer_name: 'Contract Test', payment_method: 'mtn_momo', channel: 'web',
  items: [{ product_id: 6, quantity: 1 }, { product_id: 15, quantity: 1 }], ...overrides,
});

async function stockOf(branch, productId) {
  const res = await call('GET', `/api/branches/${branch}/stock`);
  assert.equal(res.status, 200, `GET /api/branches/${branch}/stock`);
  assertShape(res.json, 'branch-stock');
  return res.json.data.find((row) => row.product_id === productId).quantity;
}

async function login(account = MANAGER) {
  const res = await call('POST', '/api/login', { body: account });
  assert.equal(res.status, 200, `${account.email} should be able to log in (see "Demo accounts" in contract/README.md)`);
  return res.json.token;
}

// ---------- Given: these four pass against the stand-in API ----------

test('GET /api/products lists every product in the agreed shape', async () => {
  const all = await call('GET', '/api/products');
  assert.equal(all.status, 200);
  assertShape(all.json, 'products');
  assert.ok(all.json.data.length >= 20, 'all 20 Nkwen products');
  assert.equal(all.json.meta.count, all.json.data.length);
  const atBranch = await call('GET', '/api/products?branch=CAV');
  assert.equal(atBranch.status, 200);
  assertShape(atBranch.json, 'products-at-branch');
  assert.ok(atBranch.json.data.every((p) => p.stock > 0), '?branch= lists only products in stock there');
});

test('GET /api/branches lists the five branches, with Limbe still planned', async () => {
  const res = await call('GET', '/api/branches');
  assert.equal(res.status, 200);
  assertShape(res.json, 'branches');
  assert.equal(res.json.data.length, 5);
  assert.equal(res.json.data.find((b) => b.code === 'LMB').status, 'planned');
});

test('POST /api/orders creates an order, and the server computes every price', async () => {
  const res = await call('POST', '/api/orders', { body: newOrder(), headers: { 'Idempotency-Key': randomUUID() } });
  assert.equal(res.status, 201);
  assertShape(res.json, 'order-created');
  const order = res.json.data;
  created.push(order.id);
  assert.equal(res.headers.get('location'), `/api/orders/${order.id}`);
  assert.equal(order.status, 'awaiting_payment', 'mobile money orders wait for payment');
  for (const item of order.items) {
    const product = (await call('GET', `/api/products/${item.product_id}`)).json.data;
    assert.equal(item.unit_price_fcfa, product.price_fcfa, `the unit price of ${product.name} comes from the server`);
    assert.equal(item.line_total_fcfa, item.unit_price_fcfa * item.quantity);
  }
  assert.equal(order.total_fcfa, order.items.reduce((sum, i) => sum + i.line_total_fcfa, 0));
});

test('POST /api/orders refuses a bad order with 422 and an error for each bad field', async () => {
  const res = await call('POST', '/api/orders', { body: { branch: 'LMB', customer_name: '', payment_method: 'bitcoin', items: [] } });
  assert.equal(res.status, 422);
  assertShape(res.json, 'error-422');
  for (const field of ['branch', 'customer_name', 'payment_method', 'items']) {
    const messages = res.json.errors[field];
    assert.ok(Array.isArray(messages) && typeof messages[0] === 'string', `errors.${field} should be a list of messages`);
  }
});

// ---------- Written in Integration & Testing (one owner each) ----------
// Each test below is marked "todo": it shows as TODO and does not fail the run yet.
// To write one: delete the { todo: ... } part, replace assert.fail with real checks,
// and put your GitHub username after "Owner:". Use the four tests above as models.

test('409 when the branch does not have enough stock', { todo: 'Integration & Testing' }, async () => {
  // Owner: @<frontend member> (the shop must show "Only N left").
  // 1. Read how many Rice 25kg (product 5) Nkwen (NKW) has: stockOf('NKW', 5).
  // 2. Order one more than that. Expect 409, the shape of error-409.json,
  //    and product_id, requested and available with the right numbers.
  // 3. Order the same product in TWO lines that together are too many. Expect 409 too.
  // 4. Check that the stock did not change.
  assert.fail('Not written yet');
});

test('the same Idempotency-Key gives the same order, and the stock moves only once', { todo: 'Integration & Testing' }, async () => {
  // Owner: @<mobile member> (the rep app retries on bad networks).
  // 1. Read the stock of Rice 50kg (product 6) at CAV.
  // 2. Send the same order twice with the same 'Idempotency-Key' header (use randomUUID()).
  // 3. Expect 201 then 200, and the SAME order id both times. Add the id to `created`.
  // 4. Check the stock went down by 1, not 2.
  assert.fail('Not written yet');
});

test('GET /api/orders/{id} returns the order, or 404 for an unknown id', { todo: 'Integration & Testing' }, async () => {
  // Owner: @<frontend member> (the order tracking page).
  // 1. Create a cash order, add its id to `created`, then read it back: 200, the
  //    shape of order-created.json, and status 'pay_at_counter'.
  // 2. Read ORD-NOTFOUND: expect 404 and the shape of error-404.json.
  assert.fail('Not written yet');
});

test('staff endpoints need a token, and the demo manager can log in and list orders', { todo: 'Integration & Testing' }, async () => {
  // Owner: @<backend member>.
  // 1. GET /api/orders?branch=NKW with no token: expect 401 (error-401.json).
  // 2. Log in with a wrong password: expect 401 (or 422).
  // 3. Log in as the demo manager (MANAGER, Nkwen): expect 200, the shape of login.json,
  //    and data.role 'branch_manager', data.branch 'NKW'. The token is res.json.token.
  // 4. Create an NKW order, then list NKW orders with the token: your order is in the list.
  // 5. List CAV orders with the same token: expect 403 (not her branch).
  assert.fail('Not written yet');
});

test('cancelling returns the stock, and a cancelled order cannot be delivered', { todo: 'Integration & Testing' }, async () => {
  // Owner: @<backend member> with @<whoever builds the manager page>.
  // 1. Log in as MANAGER. Read the stock of Palm Oil 5L (product 15) at NKW. Create an NKW order.
  // 2. As CASHIER, PATCH it to 'cancelled': expect 403 (cashiers cannot cancel).
  // 3. As the manager, PATCH it to the unknown status 'shipped': expect 422.
  // 4. PATCH it to 'cancelled': expect 200, and the stock is back to what it was.
  // 5. PATCH it to 'delivered': expect 409.
  assert.fail('Not written yet');
});
