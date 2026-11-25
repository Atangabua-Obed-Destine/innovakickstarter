// Self-check for catalogue.js. Run in this folder:  node --test
const test = require('node:test');
const assert = require('node:assert/strict');
const { NKWEN } = require('./products.js');
const catalogue = require('./catalogue.js');
const { filterProducts } = catalogue;

const all = NKWEN.products;
const names = list => list.map(p => p.name);

test('no search and "All" shows every product', () => {
  assert.equal(filterProducts(all, {}).length, all.length);
  assert.equal(filterProducts(all, { search: '', category: 'All' }).length, all.length);
});

test('search ignores case and spaces around the text', () => {
  const lower = names(filterProducts(all, { search: 'beans' }));
  assert.ok(lower.length > 0, '"beans" should find something');
  assert.ok(lower.every(n => n.toLowerCase().includes('beans')), 'Every result must contain the search text');
  assert.deepEqual(names(filterProducts(all, { search: '  BEANS ' })), lower, 'Use trim() and toLowerCase()');
});

test('category keeps only that category', () => {
  const dairy = filterProducts(all, { category: 'Dairy' });
  assert.ok(dairy.length > 0);
  assert.ok(dairy.every(p => p.category === 'Dairy'));
  assert.equal(dairy.length, all.filter(p => p.category === 'Dairy').length, 'Do not lose any Dairy product');
});

test('search and category work together', () => {
  const result = filterProducts(all, { search: 'oil', category: 'Household' });
  assert.deepEqual(result, [], 'No household product has "oil" in its name');
  const both = filterProducts(all, { search: 'e', category: 'Produce' });
  assert.ok(both.every(p => p.category === 'Produce' && p.name.toLowerCase().includes('e')));
});

test('a search that matches nothing returns an empty array', () => {
  assert.deepEqual(filterProducts(all, { search: 'pizza' }), []);
});

test('filterProducts does not change the products array', () => {
  const before = JSON.stringify(all);
  filterProducts(all, { search: 'rice', category: 'Grains' });
  assert.equal(JSON.stringify(all), before);
});

test('STRETCH: sortProducts sorts a copy', { skip: typeof catalogue.sortProducts !== 'function' && 'sortProducts not written yet (optional)' }, () => {
  const orderBefore = names(all);
  const byPrice = catalogue.sortProducts(all, 'price');
  assert.notEqual(byPrice, all, 'Return a new array');
  for (let i = 1; i < byPrice.length; i++) assert.ok(byPrice[i - 1].price_fcfa <= byPrice[i].price_fcfa, 'Cheapest first');
  const dear = catalogue.sortProducts(all, '-price');
  assert.ok(dear[0].price_fcfa >= dear[dear.length - 1].price_fcfa, 'Most expensive first');
  const byName = names(catalogue.sortProducts(all, 'name'));
  assert.deepEqual(byName, [...byName].sort((a, b) => a.localeCompare(b)), 'A to Z');
  assert.deepEqual(names(all), orderBefore, 'Sort a copy ([...products]): the original order must not change');
});
