// Self-checks for Restock Alerts. Run them with:  node --test
// They check that your code follows the rule. They do not tell you
// which products need restocking: your report does that.
// Do not change this file.

const test = require('node:test');
const assert = require('node:assert');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const { stockStatus, products } = require('./restock.js');

const WORDS = ['OK', 'LOW', 'RESTOCK'];

function check(quantity, level, expected, hint) {
  const got = stockStatus(quantity, level);
  assert.ok(WORDS.includes(got),
    `stockStatus(${quantity}, ${level}) returned ${JSON.stringify(got)}. It must return 'OK', 'LOW' or 'RESTOCK'.`);
  assert.strictEqual(got, expected, `stockStatus(${quantity}, ${level}) should be '${expected}'. ${hint}`);
}

test('plenty of stock is OK', () => {
  check(50, 10, 'OK', 'Fifty is more than twice the reorder level.');
  check(15, 7, 'OK', 'Fifteen is more than twice 7 (14).');
});

test('LOW: above the reorder level, but not more than twice it', () => {
  check(8, 7, 'LOW', 'Eight is above 7 but not more than 14.');
  check(14, 7, 'LOW', 'Exactly twice the reorder level still counts as LOW ("at or below").');
});

test('RESTOCK: at or below the reorder level', () => {
  check(7, 7, 'RESTOCK', 'Exactly at the reorder level counts ("at or below").');
  check(0, 4, 'RESTOCK', 'An empty shelf must be restocked.');
});

test('the "both" case: low AND at the reorder level', () => {
  check(3, 7, 'RESTOCK',
    'Three is at or below 7 AND at or below 14, so both rules are true. Which check runs first in your code? (Remember FizzBuzz.)');
});

function runReport() {
  return execFileSync(process.execPath, [path.join(__dirname, 'restock.js')], { encoding: 'utf8' });
}

test('the report prints one correct line per product', () => {
  const lines = runReport().split(/\r?\n/);
  const productLines = lines.filter((line) => /^(OK|LOW|RESTOCK) - /.test(line));
  assert.strictEqual(productLines.length, products.length,
    `Expected ${products.length} lines like "LOW - Example product: 15 left (reorder level 10)" but found ${productLines.length}. Did you loop over every product?`);
  for (const p of products) {
    const line = productLines.find((l) => l.includes(` - ${p.name}: `));
    assert.ok(line, `No line found for "${p.name}". Use the exact format from README.md.`);
    const expected = `${stockStatus(p.quantity, p.reorderLevel)} - ${p.name}: ${p.quantity} left (reorder level ${p.reorderLevel})`;
    assert.strictEqual(line.trim(), expected,
      `The line for "${p.name}" should use your stockStatus() result and the exact format from README.md.`);
  }
});

test('the summary line adds up', () => {
  const output = runReport();
  const match = output.match(/Summary: (\d+) OK, (\d+) LOW, (\d+) RESTOCK/);
  assert.ok(match, 'Print a last line like "Summary: 1 OK, 2 LOW, 3 RESTOCK".');
  const [ok, low, restock] = match.slice(1).map(Number);
  assert.strictEqual(ok + low + restock, products.length, 'The three counts should add up to the number of products.');
  const count = (word) => (output.match(new RegExp(`^${word} - `, 'gm')) || []).length;
  assert.deepStrictEqual([ok, low, restock], [count('OK'), count('LOW'), count('RESTOCK')],
    'The summary counts should match the product lines printed above it.');
});
