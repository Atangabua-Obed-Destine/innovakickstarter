// Stock after the week's sales, and the restock rule from Chapter 1.

// 1. Mrs. Enow Patience's rule (Restock Alerts):
//    'RESTOCK' at or below the reorder level, 'LOW' at or below twice it, else 'OK'.
function stockStatus(quantity, reorderLevel) {
  // TODO
}

// 2. For ONE branch, return one row per product (same order as `products`):
//    { id, name, start, sold, left, oversoldBy }
//    start      = the product's stock at that branch in nkwen.json (Monday morning)
//    sold       = units of that product sold at that branch (in `sales`)
//    left       = start - sold, but never below 0
//    oversoldBy = how many more were sold than we had (0 if none)
//    `sales` contains only VALID sales (see sales.js).
function stockAfterSales(products, sales, branchCode) {
  // TODO
}

module.exports = { stockStatus, stockAfterSales };
