// Self-check for basket.js. Run in this folder:  node --test
// These tests use the Bafoussam Centre (BAF) and Mile 4 (ML4) branches.
const test = require('node:test');
const assert = require('node:assert/strict');
const { NKWEN } = require('./products.js');
const B = require('./basket.js');

const product = id => NKWEN.products.find(p => p.id === id);
const beansWhite = product(4);   // BAF stock: 3
const beansRed = product(3);     // BAF stock: 0
const palmOil5 = product(15);    // BAF stock: 84
const matches = product(12);
const vegOil = product(16);

test('maxAllowed is the smaller of stock and 10', () => {
  assert.equal(B.maxAllowed(beansWhite, 'BAF'), 3, 'Only 3 in stock, so only 3 allowed');
  assert.equal(B.maxAllowed(palmOil5, 'BAF'), 10, '84 in stock, but the order limit is 10');
  assert.equal(B.maxAllowed(beansRed, 'BAF'), 0, 'Out of stock means 0');
  assert.equal(B.maxAllowed(palmOil5, 'LMB'), 0, 'Limbe has no stock yet: a missing branch is 0');
});

test('addItem adds one unit at a time', () => {
  let basket = B.addItem([], palmOil5, 'BAF');
  assert.deepEqual(basket, [{ id: 15, qty: 1 }]);
  basket = B.addItem(basket, palmOil5, 'BAF');
  assert.deepEqual(basket, [{ id: 15, qty: 2 }], 'Same product: raise qty, do not add a second line');
});

test('addItem stops at the limit', () => {
  let basket = [];
  for (let i = 0; i < 5; i++) basket = B.addItem(basket, beansWhite, 'BAF');
  assert.deepEqual(basket, [{ id: 4, qty: 3 }], 'Never more than the stock');
  assert.deepEqual(B.addItem([], beansRed, 'BAF'), [], 'Out of stock: nothing is added');
});

test('setQuantity sets, caps and removes', () => {
  assert.deepEqual(B.setQuantity([], palmOil5, 'BAF', '4'), [{ id: 15, qty: 4 }], 'Text from an input box must work: use Number()');
  assert.deepEqual(B.setQuantity([{ id: 15, qty: 4 }], palmOil5, 'BAF', 25), [{ id: 15, qty: 10 }], 'Cap at the limit');
  assert.deepEqual(B.setQuantity([{ id: 15, qty: 4 }], palmOil5, 'BAF', 0), [], '0 removes the line');
  assert.deepEqual(B.setQuantity([{ id: 15, qty: 4 }], palmOil5, 'BAF', -2), [], 'Below 0 also removes it');
});

test('setQuantity ignores numbers that are not whole', () => {
  const basket = [{ id: 15, qty: 4 }];
  assert.deepEqual(B.setQuantity(basket, palmOil5, 'BAF', 2.5), basket);
  assert.deepEqual(B.setQuantity(basket, palmOil5, 'BAF', 'abc'), basket);
});

test('no function changes the basket it receives', () => {
  const basket = [{ id: 15, qty: 4 }, { id: 4, qty: 1 }];
  const before = JSON.stringify(basket);
  B.addItem(basket, palmOil5, 'BAF');
  B.setQuantity(basket, beansWhite, 'BAF', 3);
  B.removeItem(basket, 15);
  B.revalidate(basket, NKWEN.products, 'ML4');
  assert.equal(JSON.stringify(basket), before, 'Return a new array: use map, filter or [...basket]');
});

test('itemCount adds up the units', () => {
  assert.equal(B.itemCount([]), 0);
  assert.equal(B.itemCount([{ id: 4, qty: 3 }, { id: 15, qty: 10 }]), 13);
});

test('basketLines and basketTotal use the real prices', () => {
  const basket = [{ id: 12, qty: 4 }, { id: 16, qty: 2 }, { id: 99, qty: 1 }];
  const lines = B.basketLines(basket, NKWEN.products);
  assert.equal(lines.length, 2, 'Product 99 does not exist: skip it');
  assert.equal(lines[0].name, matches.name);
  assert.equal(lines[0].line_total, matches.price_fcfa * 4);
  assert.equal(lines[1].line_total, vegOil.price_fcfa * 2);
  assert.equal(B.basketTotal(basket, NKWEN.products), matches.price_fcfa * 4 + vegOil.price_fcfa * 2);
  assert.equal(B.basketTotal([], NKWEN.products), 0, 'An empty basket costs 0');
});

test('revalidate fits the basket to a new branch and reports changes', () => {
  const basket = [{ id: 15, qty: 10 }, { id: 4, qty: 5 }, { id: 3, qty: 2 }];
  const result = B.revalidate(basket, NKWEN.products, 'BAF');
  assert.deepEqual(result.basket, [{ id: 15, qty: 10 }, { id: 4, qty: 3 }], 'Cap Beans (White) at 3; drop Beans (Red), which is out of stock');
  assert.deepEqual(result.changes, [
    { id: 4, name: 'Beans (White)', from: 5, to: 3 },
    { id: 3, name: 'Beans (Red)', from: 2, to: 0 },
  ], 'Only the lines that changed, in basket order');
});
