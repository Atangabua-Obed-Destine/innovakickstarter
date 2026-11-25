// Self-check for format.js. Run in this folder:  node --test
const test = require('node:test');
const assert = require('node:assert/strict');
const { formatFcfa, stockStatus, stockText } = require('./format.js');

test('formatFcfa adds thousands separators and the currency', () => {
  assert.equal(formatFcfa(1500), '1,500 FCFA', 'Thousands need a comma: 1,500 FCFA');
  assert.equal(formatFcfa(1234567), '1,234,567 FCFA', 'Millions need two commas');
});

test('formatFcfa keeps small amounts and zero readable', () => {
  assert.equal(formatFcfa(100), '100 FCFA');
  assert.equal(formatFcfa(0), '0 FCFA', 'An empty basket costs "0 FCFA"');
});

test('stockStatus follows the three stock levels', () => {
  assert.equal(stockStatus(0), 'out', '0 units means out of stock');
  assert.equal(stockStatus(1), 'low', '1 unit is low');
  assert.equal(stockStatus(5), 'low', '5 is still low: check your boundary (<= not <)');
  assert.equal(stockStatus(6), 'in', '6 or more is in stock');
});

test('stockText shows the words customers read', () => {
  assert.equal(stockText(0), 'Out of stock');
  assert.equal(stockText(2), 'Only 2 left', 'Use the real number of units left');
  assert.equal(stockText(40), 'In stock');
});
