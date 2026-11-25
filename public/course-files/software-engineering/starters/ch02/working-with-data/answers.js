// Problem Solving 102: answer the managers' questions from last week's sales.
// Data: sales.json (one object per sale) and nkwen.json (products and branches).
// For EVERY function: write your approach as a comment BEFORE the code.
// Run your answers:  node answers.js      Run the self-checks:  node --test

const { products } = require('./nkwen.json');
const sales = require('./sales.json');

// Done for you: the revenue (FCFA) of one sale = product price x quantity.
function lineRevenue(sale, products) {
  const product = products.find((p) => p.id === sale.productId);
  return product.price_fcfa * sale.quantity;
}

// Q1. Revenue per branch: an object like { NKW: 0, ML4: 0, ... }.
// Approach:
function revenueByBranch(sales, products) {
  // TODO
}

// Q2a. Units sold per product name: { 'Bread (loaf)': 0, ... }.
// Approach:
function unitsByProduct(sales, products) {
  // TODO
}

// Q2b. Revenue per product name: { 'Bread (loaf)': 0, ... }.
// Approach:
function revenueByProduct(sales, products) {
  // TODO
}

// Q2c. The key with the biggest value in an object like the ones above:
//      returns { name: ..., total: ... }. Works for units AND revenue.
// Approach:
function biggest(totals) {
  // TODO
}

// Q3. Revenue per day: { '2026-11-16': 0, ... }, in date order.
//     Tip: sale.date.slice(0, 10) gives the 'YYYY-MM-DD' part.
// Approach:
function revenueByDay(sales, products) {
  // TODO
}

// Q4. Payments: { cash: { count: 0, revenue: 0 }, mtn_momo: { ... }, ... }.
// Approach:
function paymentSummary(sales, products) {
  // TODO
}

// Q5. Out of stock today (from nkwen.json, not from sales):
//     a list like ['Bread (loaf) at ML4', ...], branch by branch.
// Approach:
function outOfStock(products) {
  // TODO
}

function main() {
  console.log('Nkwen Traders: last week in numbers');
  // TODO: print a clear answer to each question (Q1 to Q5), with labels.
  // Add the day name for Q3 if you like (your dayName() from the Money lesson).
}

module.exports = {
  lineRevenue, revenueByBranch, unitsByProduct, revenueByProduct, biggest,
  revenueByDay, paymentSummary, outOfStock,
};
if (require.main === module) {
  main();
}
