// Tests for the shape checker itself. They need no API and no network.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { shapeProblems } from './shape.mjs';

const product = { id: 6, name: 'Rice 50kg', price_fcfa: 36005, currency: 'FCFA' };

test('same keys and kinds: no problems, even when the values differ', () => {
  assert.deepEqual(shapeProblems(product, { id: 15, name: 'Palm Oil 5L', price_fcfa: 6600, currency: 'FCFA' }), []);
});

test('an extra key is fine: adding a field does not break anyone', () => {
  assert.deepEqual(shapeProblems(product, { ...product, stock: 58 }), []);
});

test('a missing or renamed key is a problem', () => {
  const { price_fcfa, ...rest } = product;
  assert.deepEqual(shapeProblems(product, { ...rest, price: price_fcfa }), ['body.price_fcfa is missing']);
});

test('a price sent as text, or with decimals, is a problem', () => {
  assert.equal(shapeProblems(product, { ...product, price_fcfa: '36,005 FCFA' }).length, 1);
  assert.match(shapeProblems(product, { ...product, price_fcfa: 36005.5 })[0], /whole number/);
});

test('every item of a list is checked against the first example item', () => {
  const expected = { data: [product] };
  const actual = { data: [product, { ...product, id: '15' }] };
  assert.deepEqual(shapeProblems(expected, actual), ['body.data[1].id should be a whole number, but it is a string ("15")']);
});
