// Self-checks for pricing.js. Run them with:  node --test
// Do not change this file. Read the messages when a check fails.

const test = require('node:test');
const assert = require('node:assert');
const pricing = require('./pricing.js');

const small = [
  { name: 'Sugar 1kg', price: 760, quantity: 2 },
  { name: 'Soap (bar)', price: 410, quantity: 3 },
];
const big = [
  { name: 'Rice 50kg', price: 36005, quantity: 2 },
  { name: 'Matches (box)', price: 100, quantity: 10 },
];

test('every function returns a number (it does not just print)', () => {
  const results = {
    lineTotal: pricing.lineTotal(600, 3),
    basketSubtotal: pricing.basketSubtotal(small),
    wholesaleDiscount: pricing.wholesaleDiscount(1000),
    vatIncluded: pricing.vatIncluded(1000),
    amountToPay: pricing.amountToPay(small),
  };
  for (const [name, value] of Object.entries(results)) {
    assert.strictEqual(typeof value, 'number', `${name}() returned ${value}. Use "return", not console.log().`);
  }
});

test('lineTotal multiplies price by quantity', () => {
  assert.strictEqual(pricing.lineTotal(600, 3), 1800);
  assert.strictEqual(pricing.lineTotal(36005, 0), 0, 'Zero items cost 0 FCFA.');
});

test('basketSubtotal adds every line', () => {
  assert.strictEqual(pricing.basketSubtotal([]), 0, 'An empty basket costs 0 FCFA.');
  assert.strictEqual(pricing.basketSubtotal(small), 2750);
});

test('wholesaleDiscount: 10% from 50,000 FCFA', () => {
  assert.strictEqual(pricing.wholesaleDiscount(49999), 0, 'Below 50,000 there is no discount.');
  assert.strictEqual(pricing.wholesaleDiscount(50000), 5000, '"50,000 or more" includes exactly 50,000.');
  assert.strictEqual(pricing.wholesaleDiscount(73010), 7301);
});

test('vatIncluded finds the VAT inside a shelf price', () => {
  assert.strictEqual(pricing.vatIncluded(11925), 1925);
  assert.strictEqual(pricing.vatIncluded(600), 97, 'Round to the nearest franc with Math.round().');
});

test('amountToPay reuses the other functions', () => {
  for (const basket of [small, big]) {
    const expected = pricing.basketSubtotal(basket) - pricing.wholesaleDiscount(pricing.basketSubtotal(basket));
    assert.strictEqual(pricing.amountToPay(basket), expected, 'amountToPay = subtotal minus the wholesale discount.');
  }
});
