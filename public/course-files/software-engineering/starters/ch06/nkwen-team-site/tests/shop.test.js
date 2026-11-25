// Tests for js/shop.js. Run from the project folder:  node --test
const test = require('node:test');
const assert = require('node:assert');
const { NKWEN } = require('../js/data.js');
const shop = require('../js/shop.js');

test('formatFcfa adds separators and the currency', () => {
  assert.strictEqual(shop.formatFcfa(36005), '36,005 FCFA');
});

test('productsInCategory returns one category in A-Z order', () => {
  const oils = shop.productsInCategory(NKWEN.products, 'Oils');
  assert.ok(oils.length > 0 && oils.every((p) => p.category === 'Oils'));
  const names = oils.map((p) => p.name);
  assert.deepStrictEqual(names, [...names].sort((a, b) => a.localeCompare(b)));
});
