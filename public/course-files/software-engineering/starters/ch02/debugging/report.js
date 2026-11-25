// Branch report helpers. A colleague wrote them in a hurry.
// The tests in report.test.js describe what each function SHOULD do.

// The product with this id, or undefined.
function findProduct(products, id) {
  return product.find((p) => p.id === id);
}

// Only the sales of one branch. Must not change the sales array.
function salesForBranch(sales, branchCode) {
  return sales.filter((sale) => (sale.branch = branchCode));
}

// The total number of units in a list of sales.
function totalUnits(sales) {
  let units = 0;
  for (let i = 0; i <= sales.length; i++) {
    units = units + sales[i].quantity;
  }
  return units;
}

// How many units of one product were sold.
function unitsSold(sales, productId) {
  let units = 0;
  for (const sale of sales) {
    if (sale.productId === productId) {
      units = units + sale.quantity;
      return units;
    }
  }
  return units;
}

// The revenue (FCFA) of one branch.
function branchRevenue(sales, products, branchCode) {
  let revenue = 0;
  for (const sale of salesForBranch(sales, branchCode)) {
    revenue = revenue + findProduct(products, sale.productId).price_fcfa * sale.quantity;
  }
  return revenue;
}

module.exports = { findProduct, salesForBranch, totalUnits, unitsSold, branchRevenue };
