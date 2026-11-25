// Run:  node explore.js
// Predict each line before you run it. Some answers will surprise you.

// Decimals are stored in binary, so some are not exact.
console.log('A:', 0.1 + 0.2);

// Whole numbers (integers) are exact. Money in FCFA has no cents: use integers.
console.log('B:', 100 + 200);

// Rounding to the nearest franc.
console.log('C:', Math.round(953.5), Math.round(952.75), Math.floor(952.75));

// Formatting numbers for people.
console.log('D:', (36005).toLocaleString('en-US'));
console.log('E:', new Intl.NumberFormat('fr-CM', { style: 'currency', currency: 'XAF' }).format(36005));

// The space in E looks normal, but it is a special "narrow no-break space".
const e = new Intl.NumberFormat('fr-CM', { style: 'currency', currency: 'XAF' }).format(36005);
console.log('F:', e === '36 005 FCFA', JSON.stringify(e));

// Text that looks like a number is still text.
console.log('G:', '6600' + 100, Number('6600') + 100, Number('6,600'));

// Dates: months start at 0, so 10 means November.
const sale = new Date('2026-11-16T09:12');
console.log('H:', sale.getFullYear(), sale.getMonth(), sale.getDate(), sale.getDay());

// A date with no time is read as midnight in London (UTC), not in Bamenda.
console.log('I:', new Date('2026-11-16').toString());
