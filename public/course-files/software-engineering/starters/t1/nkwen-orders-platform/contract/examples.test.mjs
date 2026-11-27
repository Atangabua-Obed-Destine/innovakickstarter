// Checks that the example answers in contract/examples agree with the contract's rules
// and with each other. Every team copies these examples into its own tests, so a wrong
// example would teach three teams the same mistake. Run: node --test "contract/*.test.mjs"
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, readdirSync } from 'node:fs';

const dir = new URL('./examples/', import.meta.url);
const example = (name) => JSON.parse(readFileSync(new URL(`${name}.json`, dir), 'utf8'));
const STATUSES = ['pay_at_counter', 'awaiting_payment', 'paid', 'delivered', 'cancelled'];
const PAYMENTS = ['cash', 'mtn_momo', 'orange_money', 'bank_transfer'];

test('every example file is valid JSON', () => {
  for (const file of readdirSync(dir).filter((f) => f.endsWith('.json'))) {
    assert.doesNotThrow(() => JSON.parse(readFileSync(new URL(file, dir), 'utf8')), `${file} is not valid JSON`);
  }
});

test('products use whole FCFA prices and the FCFA currency', () => {
  for (const name of ['products', 'products-at-branch']) {
    const { data, meta } = example(name);
    assert.equal(meta.count, data.length, `${name}: meta.count should equal the number of products`);
    for (const p of data) {
      assert.ok(Number.isInteger(p.price_fcfa) && p.price_fcfa > 0, `${name}: ${p.name} needs a whole, positive price`);
      assert.equal(p.currency, 'FCFA');
    }
  }
});

test('the created order adds up, and its prices match the product examples', () => {
  const order = example('order-created').data;
  const prices = new Map(example('products').data.map((p) => [p.id, p.price_fcfa]));
  assert.match(order.id, /^ORD-[A-Z0-9]{8}$/);
  assert.ok(STATUSES.includes(order.status));
  assert.ok(PAYMENTS.includes(order.payment_method));
  for (const item of order.items) {
    assert.equal(item.line_total_fcfa, item.unit_price_fcfa * item.quantity, `line total for ${item.name}`);
    if (prices.has(item.product_id)) assert.equal(item.unit_price_fcfa, prices.get(item.product_id), `price of ${item.name}`);
  }
  assert.equal(order.total_fcfa, order.items.reduce((sum, i) => sum + i.line_total_fcfa, 0), 'total_fcfa');
  assert.equal(order.currency, 'FCFA');
  assert.ok(!Number.isNaN(Date.parse(order.created_at)), 'created_at must be an ISO 8601 date');
});

test('the request example matches the created order: the client sends ids and quantities, never prices', () => {
  const request = example('order-request');
  const order = example('order-created').data;
  assert.equal(request.branch, order.branch);
  assert.deepEqual(request.items, order.items.map(({ product_id, quantity }) => ({ product_id, quantity })));
  assert.ok(request.items.every((i) => Object.keys(i).length === 2), 'request items have only product_id and quantity');
});

test('error examples have the agreed fields', () => {
  const invalid = example('error-422');
  assert.equal(typeof invalid.message, 'string');
  for (const messages of Object.values(invalid.errors)) assert.ok(Array.isArray(messages) && messages.every((m) => typeof m === 'string'));
  const conflict = example('error-409');
  assert.ok(['product_id', 'requested', 'available'].every((k) => Number.isInteger(conflict[k])));
  assert.ok(conflict.requested > conflict.available, 'a 409 means more was requested than is available');
  for (const name of ['error-404', 'error-401']) assert.equal(typeof example(name).message, 'string');
});

test('the login example gives a token and the staff member, as in Chapter 9', () => {
  const login = example('login');
  assert.equal(typeof login.token, 'string', 'the token is at the top level');
  assert.equal(login.token_type, 'Bearer');
  assert.ok(['cashier', 'branch_manager', 'head_office'].includes(login.data.role));
  assert.ok(!('password' in login.data), 'never show a password, not even its hash');
});

test('stock rows use the Chapter 1 rule: at or below the reorder level = RESTOCK, at or below twice it = LOW', () => {
  for (const row of example('branch-stock').data) {
    const want = row.quantity <= row.reorder_level ? 'RESTOCK' : row.quantity <= 2 * row.reorder_level ? 'LOW' : 'OK';
    assert.equal(row.status, want, row.name);
  }
});
