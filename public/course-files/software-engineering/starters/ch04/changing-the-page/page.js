// Fill the stock board for ONE branch from the data in products.js.
// NKWEN comes from products.js. formatFcfa, stockStatus and stockText come from format.js.

const BRANCH = 'CAV';   // Commercial Avenue

// TODO 1: find the branch object for BRANCH in NKWEN.branches (use .find).
// Set the text of #board-title to "Stock board: " + the branch name.

// TODO 2: select every .product row (querySelectorAll) and loop over them.
// For each row:
//   a. Read its product id: Number(row.dataset.id)
//   b. Find the product in NKWEN.products with that id.
//   c. Put the product name in .name and the formatted price in .price
//      (use textContent, never innerHTML, for data).
//   d. Read the stock for BRANCH: product.stock[BRANCH]
//   e. Put stockText(stock) in .badge, and add ONE class to the badge:
//      'badge--' + stockStatus(stock)   (classList.add)
//   f. If the product is out of stock: add the class 'is-out' to the row
//      and disable its button (button.disabled = true).

// TODO 3: set the text of #board-note to a summary in this shape:
// "6 products: X in stock, Y low, Z out of stock."
// Count X, Y and Z inside your loop. Do not type the numbers by hand.
