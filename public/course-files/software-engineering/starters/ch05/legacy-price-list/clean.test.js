// Self-check tests for clean.js. Run:  node --test
// Tests 1-6 use made-up rows (no internet). Test 7 checks your work on the real legacy list.
const test = require('node:test');
const assert = require('node:assert');
const { parsePrice, cleanName, cleanCategory, cleanRow, cleanPriceList, compareWithCatalogue } = require('./clean.js');

const ALLOWED = ['Bakery', 'Dairy', 'Grains', 'Groceries', 'Household', 'Oils', 'Produce'];

test('1. parsePrice reads numbers and messy strings', () => {
  assert.strictEqual(parsePrice(1250.0), 1250);
  assert.strictEqual(parsePrice(-300), -300, 'Keep the minus sign; cleanRow decides what to do with it.');
  assert.strictEqual(parsePrice('2,500 FCFA'), 2500);
  assert.strictEqual(parsePrice('  12,000 fcfa '), 12000);
  assert.strictEqual(parsePrice('75 F'), 75);
  assert.strictEqual(parsePrice('980'), 980);
});

test('2. parsePrice returns null when there is no price', () => {
  for (const value of [null, undefined, '', 'free', 'N/A']) {
    assert.strictEqual(parsePrice(value), null, `parsePrice(${JSON.stringify(value)}) should be null`);
  }
});

test('3. cleanName and cleanCategory tidy text', () => {
  assert.strictEqual(cleanName('  Groundnuts   1kg '), 'Groundnuts 1kg');
  assert.strictEqual(cleanName(null), '');
  assert.strictEqual(cleanCategory('DAIRY', ALLOWED), 'Dairy');
  assert.strictEqual(cleanCategory(' produce', ALLOWED), 'Produce');
  assert.strictEqual(cleanCategory('Toys', ALLOWED), null);
  assert.strictEqual(cleanCategory(undefined, ALLOWED), null);
});

test('4. cleanRow keeps a good row and gives a clean product', () => {
  const result = cleanRow({ item: ' Groundnuts 1kg ', price: '1,150 FCFA', cat: 'grains' }, ALLOWED);
  assert.strictEqual(result.ok, true);
  assert.deepStrictEqual(result.product, { name: 'Groundnuts 1kg', price_fcfa: 1150, category: 'Grains' });
  assert.ok(Array.isArray(result.warnings));
});

test('5. cleanRow rejects rows that cannot be used, with every reason', () => {
  const noPrice = cleanRow({ item: 'Corn 1kg', price: null, cat: 'Grains' }, ALLOWED);
  assert.strictEqual(noPrice.ok, false);
  assert.deepStrictEqual(noPrice.reasons, ['missing or unreadable price']);

  const negative = cleanRow({ item: 'Corn 1kg', price: -450, cat: 'Grains' }, ALLOWED);
  assert.deepStrictEqual(negative.reasons, ['price must be more than 0']);

  const fraction = cleanRow({ item: 'Corn 1kg', price: 450.5, cat: 'Grains' }, ALLOWED);
  assert.deepStrictEqual(fraction.reasons, ['price must be a whole number']);

  const both = cleanRow({ item: '   ', price: 'unknown', cat: 'Grains' }, ALLOWED);
  assert.deepStrictEqual(both.reasons, ['missing name', 'missing or unreadable price']);
  assert.ok(both.row, 'Keep the original row so a person can fix it later.');
});

test('6. compareWithCatalogue matches names ignoring case', () => {
  const clean = [
    { name: 'GROUNDNUTS 1KG', price_fcfa: 1150, category: 'Grains' },
    { name: 'Corn 1kg', price_fcfa: 450, category: 'Grains' },
    { name: 'Honey (jar)', price_fcfa: 3000, category: 'Groceries' },
  ];
  const catalogue = [
    { name: 'Groundnuts 1kg', price_fcfa: 1150 },
    { name: 'Corn 1kg', price_fcfa: 500 },
  ];
  assert.deepStrictEqual(compareWithCatalogue(clean, catalogue), [
    { name: 'GROUNDNUTS 1KG', legacy_price: 1150, current_price: 1150, status: 'same' },
    { name: 'Corn 1kg', legacy_price: 450, current_price: 500, status: 'changed' },
    { name: 'Honey (jar)', legacy_price: 3000, current_price: null, status: 'not found' },
  ]);
});

test('7. on the real legacy list, every row lands in exactly one pile', async () => {
  const { getJson } = require('./nkwen-api.js');
  const { rows } = await getJson('/legacy/price-list');
  const { categories } = await getJson('/categories');
  const { clean, rejected } = cleanPriceList(rows, categories);
  assert.strictEqual(clean.length + rejected.length, rows.length, 'clean + rejected should equal the number of rows.');
  for (const p of clean) {
    assert.ok(p.name.length > 0, 'A clean product must have a name.');
    assert.ok(Number.isInteger(p.price_fcfa) && p.price_fcfa > 0, `Bad price kept: ${JSON.stringify(p)}`);
    assert.ok(p.category === null || categories.includes(p.category), `Unknown category kept: ${p.category}`);
  }
  for (const r of rejected) {
    assert.ok(r.reasons.length > 0, 'Every rejected row needs at least one reason.');
  }
});
