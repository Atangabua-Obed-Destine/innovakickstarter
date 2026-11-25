// Print Tuesday's report:  node run.js
// Use this file with breakpoints in the VS Code debugger.
const { loadProducts, loadSales } = require('./data.js');
const { salesForBranch, totalUnits, unitsSold, branchRevenue } = require('./report.js');

const products = loadProducts();
const sales = loadSales();

console.log('Tuesday report');
console.log('Units sold (all branches):', totalUnits(sales));
console.log('Loaves of bread sold:', unitsSold(sales, 1));
for (const code of ['NKW', 'ML4', 'CAV']) {
  const branchSales = salesForBranch(sales, code);
  console.log(`${code}: ${branchSales.length} sales, ${branchRevenue(sales, products, code)} FCFA`);
}
