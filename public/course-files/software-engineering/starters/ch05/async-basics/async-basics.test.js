// Self-check tests. They check that your functions behave correctly and agree
// with each other. They do not give you the answers. Run:  node --test
const test = require('node:test');
const assert = require('node:assert');
const { getProduct, DELAY_MS } = require('./slow-shop.js');
const { wait, priceOf, priceOrNull, basketTotalOneByOne, basketTotal } = require('./async-basics.js');

test('1. wait(ms) returns a Promise that waits about ms milliseconds', async () => {
  const p = wait(200);
  assert.ok(p instanceof Promise, 'wait() must return a Promise. Did you forget "return new Promise(...)"?');
  const start = Date.now();
  await p;
  assert.ok(Date.now() - start >= 190, 'wait(200) finished too early.');
});

test('2. priceOf(id) returns a number, the same as the product price', async () => {
  const price = await priceOf(15);
  assert.strictEqual(typeof price, 'number', 'priceOf should return a number. Did you forget "await" or "return"?');
  const product = await getProduct(15);
  assert.strictEqual(price, product.price_fcfa);
});

test('3. priceOrNull(id) returns null for an unknown id instead of crashing', async () => {
  assert.strictEqual(await priceOrNull(999), null, 'Wrap the await in try { ... } catch (error) { return null; }');
  assert.strictEqual(typeof (await priceOrNull(9)), 'number', 'A known id should still give its price.');
});

test('4. basketTotalOneByOne adds price x quantity for each line', async () => {
  assert.strictEqual(await basketTotalOneByOne([]), 0, 'An empty basket costs 0 FCFA.');
  const one = await basketTotalOneByOne([{ product_id: 13, quantity: 1 }]);
  const three = await basketTotalOneByOne([{ product_id: 13, quantity: 3 }]);
  assert.strictEqual(three, one * 3, 'Three soaps should cost three times one soap.');
});

test('5. basketTotal gives the same answer as basketTotalOneByOne', async () => {
  const items = [
    { product_id: 6, quantity: 2 },
    { product_id: 9, quantity: 5 },
    { product_id: 12, quantity: 10 },
  ];
  assert.strictEqual(await basketTotal(items), await basketTotalOneByOne(items));
  assert.strictEqual(await basketTotal([]), 0, 'An empty basket costs 0 FCFA.');
});

test('6. basketTotal asks for all products at the same time (Promise.all)', async () => {
  const items = [
    { product_id: 5, quantity: 1 },
    { product_id: 6, quantity: 1 },
    { product_id: 9, quantity: 1 },
    { product_id: 15, quantity: 1 },
  ];
  const start = Date.now();
  const total = await basketTotal(items);
  const took = Date.now() - start;
  assert.ok(typeof total === 'number' && total > 0, 'basketTotal should return the total in FCFA.');
  assert.ok(took < DELAY_MS * 2, `basketTotal took ${took} ms for 4 products. With Promise.all it should take about ${DELAY_MS} ms, not ${DELAY_MS * 4}.`);
});
