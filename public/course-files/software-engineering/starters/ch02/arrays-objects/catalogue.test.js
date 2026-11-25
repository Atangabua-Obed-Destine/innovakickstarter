// Self-checks for catalogue.js. Run them with:  node --test
// They use three small made-up products you can check by hand,
// then check that your functions agree with each other on the real data.
// Do not change this file.

const test = require('node:test');
const assert = require('node:assert');
const cat = require('./catalogue.js');
const { products } = require('./nkwen.json');

const sample = [
  { id: 1, sku: 'T-1', name: 'Test Bread', category: 'Bakery', price_fcfa: 500, stock: { NKW: 4, ML4: 0 } },
  { id: 2, sku: 'T-2', name: 'Test Oil', category: 'Oils', price_fcfa: 2000, stock: { NKW: 0, ML4: 3 } },
  { id: 3, sku: 'T-3', name: 'Test Lamp Oil', category: 'Oils', price_fcfa: 1000, stock: { NKW: 1, ML4: 0 } },
];

test('productNames keeps the order and returns names only', () => {
  assert.deepStrictEqual(cat.productNames(sample), ['Test Bread', 'Test Oil', 'Test Lamp Oil']);
  assert.strictEqual(cat.productNames(products).length, products.length, 'One name per product.');
});

test('inCategory returns whole product objects from one category', () => {
  const oils = cat.inCategory(sample, 'Oils');
  assert.ok(Array.isArray(oils), 'inCategory should return an array.');
  assert.deepStrictEqual(oils.map((p) => p.id), [2, 3]);
  assert.deepStrictEqual(cat.inCategory(sample, 'Dairy'), [], 'No match gives an empty array, not undefined.');
});

test('every real product is in exactly one category', () => {
  const categories = [...new Set(products.map((p) => p.category))];
  let total = 0;
  for (const c of categories) {
    const found = cat.inCategory(products, c);
    assert.ok(found.every((p) => p.category === c), `inCategory(products, '${c}') returned a product from another category.`);
    total += found.length;
  }
  assert.strictEqual(total, products.length, 'Adding up every category should give all the products.');
});

test('findBySku finds one product, or undefined', () => {
  assert.strictEqual(cat.findBySku(sample, 'T-2').name, 'Test Oil');
  assert.strictEqual(cat.findBySku(sample, 'NOPE'), undefined, 'A missing SKU gives undefined.');
});

test('stockValue adds price x stock at one branch', () => {
  assert.strictEqual(cat.stockValue(sample, 'NKW'), 3000, '4 x 500 + 0 x 2000 + 1 x 1000 = 3000.');
  assert.strictEqual(cat.stockValue(sample, 'ML4'), 6000);
  assert.strictEqual(cat.stockValue([], 'NKW'), 0, 'An empty list is worth 0. Did you give reduce() a starting value?');
});

test('outOfStock lists the names with 0 at a branch', () => {
  assert.deepStrictEqual(cat.outOfStock(sample, 'NKW'), ['Test Oil']);
  assert.deepStrictEqual(cat.outOfStock(sample, 'ML4'), ['Test Bread', 'Test Lamp Oil']);
});
