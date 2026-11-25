// Money and date helpers for Nkwen Traders.
// Rule: money is always a whole number of francs (an integer).
// Run the checks with:  node --test

// 1. Format an amount for a receipt: 36005 -> '36,005 FCFA'.
//    Use toLocaleString('en-US') for the commas.
function formatFcfa(amount) {
  // TODO
}

// 2. Read an amount typed by a person or found in old data, and return a number:
//    '36,005 FCFA' -> 36005, '6 600' -> 6600, '760' -> 760.
//    If the text has no digits at all (for example 'free'), return null.
//    Tip: text.replace(/[^0-9]/g, '') keeps only the digits.
function parseFcfa(text) {
  // TODO
}

// 3. A percentage of an amount, rounded to the nearest franc:
//    percentOf(19055, 5) -> 953
function percentOf(amount, percent) {
  // TODO
}

// 4. Share an amount between people (or branches) in whole francs.
//    The shares must add up EXACTLY to the total, differ by at most 1 franc,
//    and the bigger shares come first: splitEvenly(10000, 3) -> [3334, 3333, 3333]
function splitEvenly(total, parts) {
  // TODO
}

// 5. The English day name of a sale time like '2026-11-16T09:12' -> 'Monday'.
//    Tip: new Date(text).getDay() gives 0 for Sunday, 1 for Monday, ...
function dayName(dateTime) {
  // TODO
}

// 6. Whole days between two dates written 'YYYY-MM-DD':
//    daysBetween('2026-11-16', '2026-11-29') -> 13
//    Tip: subtracting two Dates gives milliseconds. One day is 24 * 60 * 60 * 1000 ms.
function daysBetween(fromDate, toDate) {
  // TODO
}

module.exports = { formatFcfa, parseFcfa, percentOf, splitEvenly, dayName, daysBetween };
