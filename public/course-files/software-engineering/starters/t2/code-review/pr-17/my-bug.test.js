// Write ONE test that fails on Fru's code and would pass on correct code.
// It is your proof in the review: "here is a bug, and here is how to see it".
// Run from the pr-17 folder:  node --test
const test = require('node:test');
const assert = require('node:assert/strict');
const { orderTotal } = require('./orders/total');

test('my bug: TODO describe the bug in plain words', () => {
  // TODO: build an order (see orders/catalogue.js for ids and prices),
  // call orderTotal(...), and assert what the CORRECT answer must be.
  // Work the correct number out by hand from the ticket in PR.md.
  assert.fail('TODO: write a test that exposes one bug in PR #17');
});
