// Self-checks for the Price & Stock Calculator. Run:  node --test
// They check the rules and that your modules agree with each other.
// They do not give the week's answers. Do not change this file.

const test = require('node:test');
const assert = require('node:assert');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const { products, branches } = require('./nkwen.json');
const sales = require('./sales.json');
const reorderLevels = require('./reorder-levels.json');
const { lineTotal, wholesaleDiscount, vatIncluded, priceBasket } = require('./pricing.js');
const { formatFcfa } = require('./money.js');
const { stockStatus, stockAfterSales } = require('./stock.js');
const { checkSale, splitSales } = require('./sales.js');
const { branchReport } = require('./report.js');

const OPEN = branches.filter((b) => b.status === 'open').map((b) => b.code);
const sum = (values) => values.reduce((a, b) => a + b, 0);
const priceOf = (id) => products.find((p) => p.id === id).price_fcfa;

// splitSales() with a clear message if it does not return { valid, invalid } yet.
function split() {
  const result = splitSales(sales, products, branches);
  assert.ok(result && Array.isArray(result.valid) && Array.isArray(result.invalid),
    'splitSales() should return { valid: [...], invalid: [...] } (see sales.js).');
  return result;
}

test('pricing rules', () => {
  assert.strictEqual(lineTotal(600, 3), 1800, 'lineTotal = unit price x quantity.');
  assert.strictEqual(wholesaleDiscount(49999), 0, 'No discount below 50,000 FCFA.');
  assert.strictEqual(wholesaleDiscount(50000), 5000, '"50,000 or more" includes 50,000.');
  assert.strictEqual(vatIncluded(11925), 1925, 'VAT inside a price = price x 19.25 / 119.25, rounded.');
  assert.strictEqual(formatFcfa(36005), '36,005 FCFA');
});

test('priceBasket builds a consistent receipt', () => {
  for (const items of [[{ productId: 12, quantity: 3 }], [{ productId: 6, quantity: 2 }, { productId: 15, quantity: 1 }]]) {
    const r = priceBasket(items, products);
    assert.ok(r && Array.isArray(r.lines), 'priceBasket should return an object with a lines array (see pricing.js).');
    assert.strictEqual(r.lines.length, items.length, 'One line per item.');
    assert.strictEqual(r.subtotal, sum(r.lines.map((l) => l.lineTotal)), 'The subtotal is the sum of the line totals.');
    assert.strictEqual(r.discount, wholesaleDiscount(r.subtotal), 'Use wholesaleDiscount(subtotal).');
    assert.strictEqual(r.total, r.subtotal - r.discount, 'total = subtotal - discount.');
    assert.strictEqual(r.vat, vatIncluded(r.total), 'vat = vatIncluded(total).');
  }
  assert.throws(() => priceBasket([{ productId: 999, quantity: 1 }], products), /Unknown product/,
    'An unknown product id should throw an error that says "Unknown product".');
});

test('stockStatus follows the Chapter 1 rule', () => {
  assert.deepStrictEqual([stockStatus(7, 7), stockStatus(3, 7), stockStatus(14, 7), stockStatus(15, 7)],
    ['RESTOCK', 'RESTOCK', 'LOW', 'OK']);
});

test('checkSale accepts good records and explains bad ones', () => {
  const good = { id: 1, date: '2026-11-23T10:00', branch: 'NKW', productId: 1, quantity: 2, payment: 'cash', customer: 'walk-in' };
  assert.strictEqual(checkSale(good, products, branches), null, 'A good sale gives null.');
  for (const bad of [{ branch: 'LMB' }, { branch: 'XYZ' }, { productId: 999 }, { quantity: 0 }, { quantity: 2.5 }, { quantity: '3' }]) {
    const reason = checkSale({ ...good, ...bad }, products, branches);
    assert.strictEqual(typeof reason, 'string', `A sale with ${JSON.stringify(bad)} should give a reason (a string).`);
  }
});

test('splitSales keeps every record exactly once', () => {
  const { valid, invalid } = split();
  assert.strictEqual(valid.length + invalid.length, sales.length, 'valid + invalid = all records.');
  assert.ok(invalid.length > 0, 'This week\'s file has some bad records. Look again.');
  assert.ok(valid.every((s) => checkSale(s, products, branches) === null), 'Every valid sale passes checkSale().');
  assert.ok(invalid.every((r) => sales.some((s) => s.id === r.id) && typeof r.reason === 'string'), 'Each invalid record needs its id and a reason.');
});

test('stockAfterSales never goes below zero and adds up', () => {
  const { valid } = split();
  for (const code of OPEN) {
    const rows = stockAfterSales(products, valid, code);
    assert.strictEqual(rows.length, products.length, `${code}: one row per product.`);
    for (const row of rows) {
      assert.ok(row.left >= 0, `${code} ${row.name}: stock left can never be negative.`);
      assert.strictEqual(row.left, Math.max(0, row.start - row.sold), `${code} ${row.name}: left = start - sold (never below 0).`);
      assert.strictEqual(row.oversoldBy, Math.max(0, row.sold - row.start), `${code} ${row.name}: oversoldBy = sold - start when positive.`);
    }
    const unitsHere = sum(valid.filter((s) => s.branch === code).map((s) => s.quantity));
    assert.strictEqual(sum(rows.map((r) => r.sold)), unitsHere, `${code}: the sold column should add up to the units sold there.`);
  }
});

test('branch reports agree with the sales and with each other', () => {
  const { valid } = split();
  let revenueAll = 0;
  for (const code of OPEN) {
    const r = branchReport(code, products, branches, valid, reorderLevels);
    assert.ok(r && r.code === code, `branchReport('${code}', ...) should return an object with code '${code}'.`);
    const here = valid.filter((s) => s.branch === code);
    assert.strictEqual(r.salesCount, here.length, `${code}: salesCount.`);
    assert.strictEqual(r.units, sum(here.map((s) => s.quantity)), `${code}: units.`);
    assert.strictEqual(r.revenue, sum(here.map((s) => lineTotal(priceOf(s.productId), s.quantity))), `${code}: revenue.`);
    const unitsOf = (name) => sum(here.filter((s) => products.find((p) => p.id === s.productId).name === name).map((s) => s.quantity));
    assert.ok(r.bestSeller && unitsOf(r.bestSeller.name) === r.bestSeller.units, `${code}: bestSeller should be { name, units } with its real units.`);
    assert.ok(products.every((p) => unitsOf(p.name) <= r.bestSeller.units), `${code}: no product sold more units than the best seller.`);
    const statusOf = (row) => stockStatus(row.left, reorderLevels[row.id]);
    assert.deepStrictEqual(r.restock, r.stock.filter((row) => statusOf(row) === 'RESTOCK').map((row) => row.name), `${code}: restock list.`);
    assert.deepStrictEqual(r.low, r.stock.filter((row) => statusOf(row) === 'LOW').map((row) => row.name), `${code}: low list.`);
    revenueAll += r.revenue;
  }
  assert.strictEqual(revenueAll, sum(valid.map((s) => lineTotal(priceOf(s.productId), s.quantity))), 'The branches add up to the company.');
});

function cli(...args) {
  return spawnSync(process.execPath, [path.join(__dirname, 'cli.js'), ...args], { encoding: 'utf8' });
}

test('the command line works and fails politely', () => {
  const price = cli('price', '6', '2', '12', '10');
  assert.strictEqual(price.status, 0, `"node cli.js price 6 2 12 10" failed:\n${price.stderr}`);
  assert.match(price.stdout, /Rice 50kg/);
  const { valid } = split();
  const nkw = branchReport('NKW', products, branches, valid, reorderLevels);
  const report = cli('report', 'NKW');
  assert.strictEqual(report.status, 0, `"node cli.js report NKW" failed:\n${report.stderr}`);
  assert.ok(report.stdout.includes(formatFcfa(nkw.revenue)), 'The NKW report should print its revenue with formatFcfa().');
  assert.ok(nkw.restock.every((name) => report.stdout.includes(name)), 'The NKW report should name every product to restock.');
  assert.strictEqual(cli('report', 'LMB').status, 1, 'A branch that is not open should give exit code 1.');
  assert.strictEqual(cli().status, 1, 'No command should print the usage and give exit code 1.');
});
