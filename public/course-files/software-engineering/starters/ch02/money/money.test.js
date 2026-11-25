// Self-checks for money.js. Run them with:  node --test
// Do not change this file.

const test = require('node:test');
const assert = require('node:assert');
const m = require('./money.js');

test('formatFcfa adds commas and the currency', () => {
  assert.strictEqual(m.formatFcfa(36005), '36,005 FCFA');
  assert.strictEqual(m.formatFcfa(100), '100 FCFA');
  assert.strictEqual(m.formatFcfa(1250000), '1,250,000 FCFA');
  assert.strictEqual(m.formatFcfa(0), '0 FCFA');
});

test('parseFcfa reads messy text into a number', () => {
  assert.strictEqual(m.parseFcfa('36,005 FCFA'), 36005);
  assert.strictEqual(m.parseFcfa('6 600'), 6600);
  assert.strictEqual(m.parseFcfa('760'), 760);
  assert.strictEqual(typeof m.parseFcfa('760'), 'number', 'Return a number, not text.');
  assert.strictEqual(m.parseFcfa('free'), null, 'No digits at all: return null.');
});

test('percentOf rounds to whole francs', () => {
  assert.strictEqual(m.percentOf(19055, 5), 953);
  assert.strictEqual(m.percentOf(20000, 10), 2000);
  assert.ok(Number.isInteger(m.percentOf(1395, 7)), 'The answer must be a whole number of francs.');
});

test('splitEvenly never loses or invents a franc', () => {
  assert.deepStrictEqual(m.splitEvenly(10000, 3), [3334, 3333, 3333]);
  assert.deepStrictEqual(m.splitEvenly(9000, 3), [3000, 3000, 3000]);
  for (const [total, parts] of [[100, 7], [36005, 4], [5, 5], [2, 4]]) {
    const shares = m.splitEvenly(total, parts);
    assert.strictEqual(shares.length, parts, `splitEvenly(${total}, ${parts}) should give ${parts} shares.`);
    assert.strictEqual(shares.reduce((a, b) => a + b, 0), total, `The shares of ${total} must add up to ${total}.`);
    assert.ok(shares.every(Number.isInteger), 'Every share must be a whole number of francs.');
    assert.ok(Math.max(...shares) - Math.min(...shares) <= 1, 'Shares may differ by at most 1 franc.');
  }
});

test('dayName gives the day of a sale', () => {
  assert.strictEqual(m.dayName('2026-11-16T09:12'), 'Monday');
  assert.strictEqual(m.dayName('2026-11-22T18:40'), 'Sunday');
  assert.strictEqual(m.dayName('2026-11-21T00:30'), 'Saturday', 'Just after midnight is still Saturday.');
});

test('daysBetween counts whole days', () => {
  assert.strictEqual(m.daysBetween('2026-11-16', '2026-11-29'), 13);
  assert.strictEqual(m.daysBetween('2026-11-30', '2026-12-01'), 1, 'Across the end of a month.');
  assert.strictEqual(m.daysBetween('2026-12-31', '2027-01-01'), 1, 'Across the end of a year.');
  assert.strictEqual(m.daysBetween('2026-11-16', '2026-11-16'), 0);
});
