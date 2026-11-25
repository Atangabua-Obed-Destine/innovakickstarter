// Your tests for till.js. Run them with:  node --test
// One example test is done for you. Replace every TODO test with real tests.
// Aim for at least 8 tests in total (see the lesson for what to include).

const { describe, test } = require('node:test');
const assert = require('node:assert');
const { lineTotal, wholesaleDiscount, sell } = require('./till.js');

describe('lineTotal', () => {
  test('multiplies the unit price by the quantity', () => {
    // Arrange: the inputs. Act: call the function. Assert: check the answer.
    const result = lineTotal(600, 3);
    assert.strictEqual(result, 1800);
  });

  test('TODO: a quantity that is not allowed throws an error', () => {
    assert.fail('TODO: write this test with assert.throws(() => ..., /Quantity/)');
  });
});

describe('wholesaleDiscount', () => {
  test('TODO: the edges of the 50,000 FCFA rule', () => {
    assert.fail('TODO: test just below, exactly at and above 50,000 FCFA');
  });
});

describe('sell', () => {
  test('TODO: selling reduces the stock', () => {
    assert.fail('TODO: write this test');
  });

  test('TODO: selling more than the stock throws an error', () => {
    assert.fail('TODO: write this test with assert.throws');
  });
});
