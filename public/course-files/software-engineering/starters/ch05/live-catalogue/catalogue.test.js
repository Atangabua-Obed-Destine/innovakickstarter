// Self-check tests for catalogue.js (no internet needed). Run:  node --test
const test = require('node:test');
const assert = require('node:assert');
const c = require('./catalogue.js');

const bread = { id: 101, name: 'Test Bread', price_fcfa: 650 };
const milk = { id: 102, name: 'Test Milk', price_fcfa: 1200 };

test('1. filtersToParams trims and drops empty filters', () => {
  assert.deepStrictEqual(c.filtersToParams({ search: '  milk ', category: '', branch: 'ML4', sort: undefined }),
    { search: 'milk', branch: 'ML4' });
  assert.deepStrictEqual(c.filtersToParams({ search: '   ', category: 'Dairy' }), { category: 'Dairy' },
    'A search of only spaces is empty.');
});

test('2. addToBasket returns a new basket and merges the same product', () => {
  const empty = [];
  const one = c.addToBasket(empty, bread);
  assert.strictEqual(empty.length, 0, 'Do not change the old basket; return a new array.');
  assert.deepStrictEqual(one, [{ product_id: 101, name: 'Test Bread', price_fcfa: 650, quantity: 1 }]);
  const more = c.addToBasket(one, bread, 2);
  assert.strictEqual(more.length, 1, 'The same product should stay on one line.');
  assert.strictEqual(more[0].quantity, 3);
  assert.strictEqual(one[0].quantity, 1, 'Do not change the old line either.');
});

test('3. removeFromBasket and basketTotal', () => {
  let basket = c.addToBasket([], bread, 2);
  basket = c.addToBasket(basket, milk, 1);
  assert.strictEqual(c.basketTotal(basket), 2 * 650 + 1200);
  basket = c.removeFromBasket(basket, 101);
  assert.deepStrictEqual(basket.map((l) => l.product_id), [102]);
  assert.strictEqual(c.basketTotal([]), 0);
});

test('4. orderPayload has exactly what POST /orders needs', () => {
  const basket = c.addToBasket(c.addToBasket([], bread, 2), milk, 1);
  assert.deepStrictEqual(c.orderPayload(basket, { branch: 'CAV', customer_name: '  Test Customer ', payment_method: 'orange_money' }), {
    branch: 'CAV',
    customer_name: 'Test Customer',
    payment_method: 'orange_money',
    items: [{ product_id: 101, quantity: 2 }, { product_id: 102, quantity: 1 }],
  });
});

test('5. describeOrderResult explains 201, 422, 409 and other answers', () => {
  const ok = c.describeOrderResult(201, {
    order: { id: 'ORD-TEST1234', total_fcfa: 2500, items: [{ product_id: 101, name: 'Test Bread', quantity: 2, unit_price_fcfa: 650, line_total_fcfa: 1300 }, { product_id: 102, name: 'Test Milk', quantity: 1, unit_price_fcfa: 1200, line_total_fcfa: 1200 }] },
    message: 'ok',
  });
  assert.strictEqual(ok.kind, 'success');
  assert.match(ok.title, /ORD-TEST1234/);
  assert.match(ok.title, /2,500/, 'Show the total with a thousands separator.');
  assert.ok(ok.lines.some((l) => /Test Bread/.test(l) && /1,300/.test(l)), 'List each item with its line total.');

  const invalid = c.describeOrderResult(422, { message: 'The order is not valid.', errors: { customer_name: ['The customer name field is required.'], branch: ['Bad branch.'] } });
  assert.strictEqual(invalid.kind, 'invalid');
  assert.deepStrictEqual(invalid.lines, ['The customer name field is required.', 'Bad branch.']);

  const stock = c.describeOrderResult(409, { message: 'Not enough Test Milk at this branch.', product_id: 102, requested: 9, available: 4 });
  assert.strictEqual(stock.kind, 'stock');
  assert.strictEqual(stock.title, 'Not enough Test Milk at this branch.');
  assert.ok(stock.lines.join(' ').includes('9') && stock.lines.join(' ').includes('4'), 'Say what was asked for and what is available.');

  for (const status of [500, 503, 0]) {
    const other = c.describeOrderResult(status, null);
    assert.strictEqual(other.kind, 'error');
    assert.match(other.title, /try again/i);
  }
});
