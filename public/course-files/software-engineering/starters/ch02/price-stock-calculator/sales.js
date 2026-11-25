// Checking the till records before we trust them.
// Real data has mistakes. A good tool reports them instead of crashing or hiding them.

// 1. Return null when the sale is fine. Otherwise return a short reason (a string):
//    - the branch must exist AND be open (status 'open' in nkwen.json)
//    - the productId must exist
//    - the quantity must be a whole number of at least 1 (a number, not text)
function checkSale(sale, products, branches) {
  // TODO
}

// 2. Split the sales into the ones we can use and the ones we must skip:
//    { valid: [sale, ...], invalid: [{ id, reason }, ...] }
//    Reuse checkSale().
function splitSales(sales, products, branches) {
  // TODO
}

module.exports = { checkSale, splitSales };
