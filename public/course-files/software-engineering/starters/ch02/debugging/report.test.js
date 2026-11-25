// What the report helpers SHOULD do. Run:  node --test
// The tests are correct. Do not change them: fix the code instead.

const test = require('node:test');
const assert = require('node:assert');
const { loadProducts, loadSales } = require('./data.js');
const r = require('./report.js');

test('findProduct finds a product by id', () => {
  assert.strictEqual(r.findProduct(loadProducts(), 6).name, 'Rice 50kg');
  assert.strictEqual(r.findProduct(loadProducts(), 99), undefined);
});

test('loadSales gives every quantity as a number', () => {
  for (const sale of loadSales()) {
    assert.strictEqual(typeof sale.quantity, 'number', `Sale ${sale.id} has quantity ${JSON.stringify(sale.quantity)}.`);
  }
});

test('salesForBranch keeps one branch and does not change the sales', () => {
  const sales = loadSales();
  const nkw = r.salesForBranch(sales, 'NKW');
  assert.strictEqual(nkw.length, 4, 'Nkwen has 4 sales on Tuesday.');
  assert.deepStrictEqual(sales.map((s) => s.branch), loadSales().map((s) => s.branch),
    'After salesForBranch() the original sales must still have their own branches.');
});

test('totalUnits adds every quantity', () => {
  assert.strictEqual(r.totalUnits([]), 0);
  assert.strictEqual(r.totalUnits(loadSales()), 37);
});

test('unitsSold counts every sale of a product', () => {
  assert.strictEqual(r.unitsSold(loadSales(), 1), 15, 'Bread was sold four times on Tuesday.');
  assert.strictEqual(r.unitsSold(loadSales(), 15), 1);
  assert.strictEqual(r.unitsSold(loadSales(), 99), 0);
});

test('branchRevenue adds price x quantity for one branch', () => {
  const products = loadProducts();
  assert.strictEqual(r.branchRevenue(loadSales(), products, 'NKW'), 41885);
  assert.strictEqual(r.branchRevenue(loadSales(), products, 'CAV'), 9400);
});
