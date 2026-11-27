// Self-checks for problems.js. Run:  node --test
// Do not change this file: make the tests pass.
const test = require('node:test');
const assert = require('node:assert/strict');
const p = require('./problems.js');

// The 20 Nkwen products (name and category), from nkwen.json.
const PRODUCTS = [
  ['Bread (loaf)', 'Bakery'], ['Milk Powder 400g', 'Dairy'], ['Beans (Red)', 'Grains'], ['Beans (White)', 'Grains'],
  ['Rice 25kg', 'Grains'], ['Rice 50kg', 'Grains'], ['Maggi Cubes (pack)', 'Groceries'], ['Salt 1kg', 'Groceries'],
  ['Sugar 1kg', 'Groceries'], ['Tomato Paste (tin)', 'Groceries'], ['Detergent 1kg', 'Household'], ['Matches (box)', 'Household'],
  ['Soap (bar)', 'Household'], ['Palm Oil 1L', 'Oils'], ['Palm Oil 5L', 'Oils'], ['Vegetable Oil 5L', 'Oils'],
  ['Cassava (bag)', 'Produce'], ['Onions 1kg', 'Produce'], ['Plantain (bunch)', 'Produce'], ['Tomatoes 1kg', 'Produce'],
].map(([name, category]) => ({ name, category }));

test('1. pairForBudget finds two prices that make the budget', () => {
  // Sugar 760, Soap 410, Palm Oil 5L 6600, Matches 100
  assert.deepEqual(p.pairForBudget([760, 410, 6600, 100], 860), [0, 3]);
  assert.deepEqual(p.pairForBudget([600, 600], 1200), [0, 1], 'two products may have the same price');
  assert.equal(p.pairForBudget([600], 1200), null, 'one product cannot be used twice');
  assert.equal(p.pairForBudget([], 500), null);
  assert.equal(p.pairForBudget([760, 410], 999), null);
});

test('1. pairForBudget is fast enough for a big price list (no nested loops)', () => {
  const prices = Array.from({ length: 40000 }, (_, i) => 2 * i + 1); // all odd
  const start = Date.now();
  const answer = p.pairForBudget(prices, 2); // impossible: the two cheapest add up to 1 + 3 = 4
  const ms = Date.now() - start;
  assert.equal(answer, null);
  assert.ok(ms < 300, `took ${ms} ms. Two nested loops check about 800 million pairs here. Use a Map.`);
});

test('2. firstOneTimeCustomer keeps the order of the day', () => {
  assert.equal(p.firstOneTimeCustomer(['Ndi', 'Mbah', 'Ndi', 'Fru', 'Mbah']), 'Fru');
  assert.equal(p.firstOneTimeCustomer(['Ayuk', 'Ayuk']), null);
  assert.equal(p.firstOneTimeCustomer([]), null);
  assert.equal(p.firstOneTimeCustomer(['Tabi']), 'Tabi');
});

test('3. countByCategory counts the Nkwen catalogue', () => {
  const counts = p.countByCategory(PRODUCTS);
  assert.equal(Object.keys(counts).length, 7);
  assert.equal(Object.values(counts).reduce((a, b) => a + b, 0), 20);
  assert.equal(counts.Bakery, 1);
  assert.deepEqual(p.countByCategory([]), {});
});

test('4. isMirrorCode ignores case, spaces and dashes', () => {
  assert.equal(p.isMirrorCode('NK-7-kn'), true);
  assert.equal(p.isMirrorCode('ab 12 21 BA'), true);
  assert.equal(p.isMirrorCode('NKW-1'), false);
  assert.equal(p.isMirrorCode(''), true, 'an empty code reads the same both ways');
});

test('5. mergeSorted merges without sorting again', () => {
  assert.deepEqual(p.mergeSorted([100, 410, 760], [305, 600, 36005]), [100, 305, 410, 600, 760, 36005]);
  assert.deepEqual(p.mergeSorted([], [355]), [355]);
  assert.deepEqual(p.mergeSorted([510, 510], [510]), [510, 510, 510]);
  const a = [1, 3];
  p.mergeSorted(a, [2]);
  assert.deepEqual(a, [1, 3], 'do not change the lists you were given');
});

test('6. bestRun finds the best days in a row', () => {
  const week = [41000, 52000, 38000, 61000, 70500, 29000, 45000];
  assert.equal(p.bestRun(week, 2), 131500);
  assert.equal(p.bestRun(week, 7), 336500);
  assert.equal(p.bestRun(week, 1), 70500);
  assert.equal(p.bestRun([1000, 2000], 3), null);
});
