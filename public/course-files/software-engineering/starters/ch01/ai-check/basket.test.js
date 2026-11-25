// Run with:  node --test
// The AI also wrote the first three tests. They pass.
// Does that prove the function is correct?

const test = require('node:test');
const assert = require('node:assert');
const { basketTotal } = require('./basket.js');

test('an empty basket costs 0 FCFA', () => {
  assert.strictEqual(basketTotal([]), 0);
});

test('a small basket gets no discount', () => {
  const basket = [
    { name: 'Sugar 1kg', price: 760, quantity: 2 },
    { name: 'Soap (bar)', price: 410, quantity: 3 },
  ];
  assert.strictEqual(basketTotal(basket), 2750);
});

test('a big wholesale basket gets 10% off', () => {
  const basket = [{ name: 'Rice 50kg', price: 36005, quantity: 2 }];
  assert.strictEqual(basketTotal(basket), 64809);
});

// YOUR TEST
// Write a test that FAILS because of the bug you found.
// Use real Nkwen products and prices, and work out the right answer by hand.
// Run node --test and see it fail. Then fix basket.js and see every test pass.
test('my test: the bug I found', () => {
  assert.fail('TODO: replace this line with your own test (see the comment above).');
});
