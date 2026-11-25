// Self-check tests that call the real course API (internet needed, about 5 requests).
// They need YOUR nkwen-api.js with a postOrder(order) function. Run:  node --test
const test = require('node:test');
const assert = require('node:assert');
const api = require('./nkwen-api.js');
const c = require('./catalogue.js');

test('A. filters reach the API as query parameters', async () => {
  const oils = await api.getProducts(c.filtersToParams({ category: 'Oils', sort: '-price', search: '' }));
  assert.ok(oils.length > 0 && oils.every((p) => p.category === 'Oils'));
  for (let i = 1; i < oils.length; i += 1) {
    assert.ok(oils[i - 1].price_fcfa >= oils[i].price_fcfa, 'sort=-price should list the most expensive first.');
  }
});

test('B. postOrder exists and returns { status, body } (it does not throw for 422 or 409)', () => {
  assert.strictEqual(typeof api.postOrder, 'function', 'Add postOrder(order) to nkwen-api.js and export it.');
});

test('C. a good order gets 201 and a correct total', async () => {
  const { status, body } = await api.postOrder({ branch: 'CAV', customer_name: 'Self-check', payment_method: 'cash', items: [{ product_id: 16, quantity: 2 }, { product_id: 13, quantity: 3 }] });
  assert.strictEqual(status, 201);
  const sum = body.order.items.reduce((s, item) => s + item.line_total_fcfa, 0);
  assert.strictEqual(body.order.total_fcfa, sum, 'The total should equal the sum of the line totals.');
  assert.strictEqual(body.order.saved, false, 'Practice API: nothing is saved.');
  assert.strictEqual(c.describeOrderResult(status, body).kind, 'success');
});

test('D. a missing customer name gets 422 with a message for that field', async () => {
  const { status, body } = await api.postOrder({ branch: 'CAV', customer_name: '', payment_method: 'cash', items: [{ product_id: 13, quantity: 1 }] });
  assert.strictEqual(status, 422);
  assert.ok(body.errors && body.errors.customer_name, 'Laravel-style errors: body.errors.customer_name');
  assert.strictEqual(c.describeOrderResult(status, body).kind, 'invalid');
});

test('E. asking for more than the branch has gets 409', async () => {
  const { status, body } = await api.postOrder({ branch: 'NKW', customer_name: 'Self-check', payment_method: 'mtn_momo', items: [{ product_id: 1, quantity: 100 }] });
  assert.strictEqual(status, 409);
  assert.ok(body.available < body.requested);
  assert.strictEqual(c.describeOrderResult(status, body).kind, 'stock');
});
