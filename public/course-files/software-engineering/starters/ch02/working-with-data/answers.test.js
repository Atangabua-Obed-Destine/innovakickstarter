// Self-checks for answers.js. Run them with:  node --test
// They check that your answers agree with each other. They do not give the answers.
// Do not change this file.

const test = require('node:test');
const assert = require('node:assert');
const a = require('./answers.js');
const { products } = require('./nkwen.json');
const sales = require('./sales.json');

const sum = (values) => values.reduce((x, y) => x + y, 0);
const totalRevenue = sum(sales.map((s) => a.lineRevenue(s, products)));
const totalUnits = sum(sales.map((s) => s.quantity));

// Checks that a function returned an object before we look inside it.
function objectFrom(fnName, value) {
  assert.ok(value && typeof value === 'object' && !Array.isArray(value),
    `${fnName}() returned ${JSON.stringify(value)}. It should return an object (see the comment above it in answers.js).`);
  return value;
}

test('Q1: the branches add up to the whole week', () => {
  const byBranch = objectFrom('revenueByBranch', a.revenueByBranch(sales, products));
  assert.strictEqual(sum(Object.values(byBranch)), totalRevenue, 'The revenue of all branches should add up to the revenue of all sales.');
  assert.deepStrictEqual(Object.keys(byBranch).sort(), [...new Set(sales.map((s) => s.branch))].sort(), 'One key per branch that has sales.');
});

test('Q2: units and revenue per product are complete', () => {
  const units = objectFrom('unitsByProduct', a.unitsByProduct(sales, products));
  const revenue = objectFrom('revenueByProduct', a.revenueByProduct(sales, products));
  assert.strictEqual(sum(Object.values(units)), totalUnits, 'unitsByProduct should count every unit once.');
  assert.strictEqual(sum(Object.values(revenue)), totalRevenue, 'revenueByProduct should add up to the week\'s revenue.');
  assert.ok(Object.keys(units).every((name) => products.some((p) => p.name === name)), 'Use product names as keys.');
});

test('Q2: biggest() picks the largest value', () => {
  assert.deepStrictEqual(a.biggest({ Soap: 3, Rice: 9, Salt: 4 }), { name: 'Rice', total: 9 },
    'biggest({ Soap: 3, Rice: 9, Salt: 4 }) should return { name: "Rice", total: 9 }.');
  const units = objectFrom('unitsByProduct', a.unitsByProduct(sales, products));
  const best = a.biggest(units);
  assert.strictEqual(best.total, Math.max(...Object.values(units)), 'The best seller should have the most units.');
  assert.strictEqual(units[best.name], best.total, 'name and total should belong together.');
});

test('Q3: days are in order and add up', () => {
  const byDay = objectFrom('revenueByDay', a.revenueByDay(sales, products));
  const days = Object.keys(byDay);
  assert.ok(days.every((d) => /^\d{4}-\d{2}-\d{2}$/.test(d)), 'Use keys like 2026-11-16.');
  assert.deepStrictEqual(days, [...days].sort(), 'Keep the days in date order.');
  assert.strictEqual(sum(Object.values(byDay)), totalRevenue, 'The days should add up to the week\'s revenue.');
});

test('Q4: every sale is counted once, with its money', () => {
  const pay = objectFrom('paymentSummary', a.paymentSummary(sales, products));
  const methods = Object.values(pay);
  assert.ok(methods.length > 0 && methods.every((m) => 'count' in m && 'revenue' in m), 'Each method needs { count, revenue }.');
  assert.strictEqual(sum(methods.map((m) => m.count)), sales.length, 'The counts should add up to the number of sales.');
  assert.strictEqual(sum(methods.map((m) => m.revenue)), totalRevenue, 'The payment revenues should add up to the week\'s revenue.');
});

test('Q5: every product listed is really out of stock', () => {
  const list = a.outOfStock(products);
  assert.ok(Array.isArray(list) && list.length > 0, 'outOfStock should return a non-empty array of texts.');
  assert.strictEqual(new Set(list).size, list.length, 'No duplicates.');
  for (const item of list) {
    const match = item.match(/^(.+) at ([A-Z0-9]{3})$/);
    assert.ok(match, `"${item}" should look like "Bread (loaf) at ML4".`);
    const product = products.find((p) => p.name === match[1]);
    assert.ok(product, `Unknown product "${match[1]}".`);
    assert.strictEqual(product.stock[match[2]], 0, `${item}: that product is not out of stock there.`);
  }
});
