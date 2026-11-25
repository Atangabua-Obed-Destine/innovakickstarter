// Catalogue functions for Nkwen Traders.
// Each product looks like this (see nkwen.json):
// { id: 6, sku: 'NKT-GRA-006', name: 'Rice 50kg', category: 'Grains', unit: 'bag',
//   price_fcfa: 36005, stock: { NKW: 42, ML4: 3, CAV: 58, BAF: 21 } }
// Run the checks with:  node --test

// 1. An array with just the product names, in the same order. Use map().
function productNames(products) {
  // TODO
}

// 2. Only the products in one category, for example 'Oils'. Use filter().
function inCategory(products, category) {
  // TODO
}

// 3. The product with this SKU, or undefined if there is none. Use find().
function findBySku(products, sku) {
  // TODO
}

// 4. The value of the stock at one branch: the sum of price_fcfa x stock
//    at that branch, for every product. Use reduce() (or a for...of loop first,
//    then rewrite it with reduce). Tip: product.stock[branchCode]
function stockValue(products, branchCode) {
  // TODO
}

// 5. The names of the products with 0 in stock at one branch.
//    Use filter() and then map().
function outOfStock(products, branchCode) {
  // TODO
}

module.exports = { productNames, inCategory, findBySku, stockValue, outOfStock };
