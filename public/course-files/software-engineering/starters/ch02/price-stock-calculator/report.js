// The weekly report for one branch. Reuse your other modules: do not copy code.
const { lineTotal } = require('./pricing.js');
const { stockStatus, stockAfterSales } = require('./stock.js');

// Build (do not print) the report of ONE branch from the VALID sales.
// `reorderLevels` looks like { "1": 10, "2": 10, ... } (product id -> level).
// Return an object shaped like this:
// {
//   code, name,         // branch code and name, e.g. 'NKW', 'Nkwen'
//   salesCount,         // number of sales at this branch
//   units,              // units sold at this branch
//   revenue,            // FCFA: sum of lineTotal(price, quantity) of its sales
//   bestSeller,         // { name, units } with the most units, or null if no sales
//   stock,              // the rows from stockAfterSales(), each with `reorderLevel` and `status` added
//   restock,            // names of the products with status 'RESTOCK'
//   low,                // names of the products with status 'LOW'
//   oversold,           // [{ name, by }] for products with oversoldBy > 0
// }
function branchReport(branchCode, products, branches, validSales, reorderLevels) {
  // TODO
}

module.exports = { branchReport };
