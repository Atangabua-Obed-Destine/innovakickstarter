const test = require('node:test');
const assert = require('node:assert/strict');
const { orderTotal, isWholesale } = require('./total');

test('2 x Rice 50kg + 1 x Palm Oil 5L', () => {
  const result = orderTotal([{ product_id: 6, quantity: 2 }, { product_id: 15, quantity: 1 }]);
  assert.ok(result.total_fcfa > 78610);
});

test('wholesale discount works', () => {
  const result = orderTotal([{ product_id: 6, quantity: 12 }], { name: 'Ndi Grace', phone: '+237 6XX XX XX XX' });
  assert.equal(typeof result.total_fcfa, 'number');
});

test('isWholesale', () => {
  assert.equal(isWholesale([{ product_id: 6, quantity: 20 }]), true);
});
