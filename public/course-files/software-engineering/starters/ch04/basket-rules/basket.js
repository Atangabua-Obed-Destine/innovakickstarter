// Basket rules for Nkwen Traders online orders.
// A basket is an array of lines: [{ id: 6, qty: 2 }, ...], one line per product.
// RULE: no function changes the basket it receives. Each returns a NEW array
// (map, filter and [...basket, newLine] all make new arrays).
// Run the self-check in this folder with:  node --test

const MAX_PER_PRODUCT = 10;   // rule: at most 10 of one product per online order

// Ready for you: how many of this product are already in the basket (0 if none).
function quantityOf(basket, id) {
  const line = basket.find(l => l.id === id);
  return line ? line.qty : 0;
}

// Ready for you: a new basket without that product.
function removeItem(basket, id) {
  return basket.filter(l => l.id !== id);
}

// TODO 1: the most a customer may have of this product at this branch:
// the SMALLER of the branch stock (product.stock[branch]) and MAX_PER_PRODUCT.
// A missing branch counts as 0 stock.
function maxAllowed(product, branch) {
  throw new Error('TODO: write maxAllowed()');
}

// TODO 2: put exactly qty of this product in the basket.
// - Convert with Number(qty). Not a whole number (2.5, 'abc') -> return basket unchanged.
// - 0 or less -> remove the line.
// - More than maxAllowed -> use maxAllowed instead.
// - New product -> add a line; product already there -> replace its qty.
function setQuantity(basket, product, branch, qty) {
  throw new Error('TODO: write setQuantity()');
}

// TODO 3: one more unit of this product. If the basket already holds
// maxAllowed, return the basket unchanged. (setQuantity can do the work.)
function addItem(basket, product, branch) {
  throw new Error('TODO: write addItem()');
}

// TODO 4: the total number of units in the basket (use reduce).
function itemCount(basket) {
  throw new Error('TODO: write itemCount()');
}

// TODO 5: lines ready to show on the page, in basket order:
// { id, name, unit, qty, price_fcfa, line_total }. Skip ids that are not in products.
function basketLines(basket, products) {
  throw new Error('TODO: write basketLines()');
}

// TODO 6: the basket total in FCFA (a whole number). Reuse basketLines.
function basketTotal(basket, products) {
  throw new Error('TODO: write basketTotal()');
}

// TODO 7: the customer switched to another branch. Fit every line to the
// new branch's maxAllowed. Remove lines that drop to 0, and lines whose
// product is unknown. Return { basket, changes }, where changes lists every
// line that changed as { id, name, from, to } (use 'Unknown product' as name).
function revalidate(basket, products, branch) {
  throw new Error('TODO: write revalidate()');
}

// This line lets Node's test runner use the functions. The browser ignores it.
if (typeof module !== 'undefined') {
  module.exports = { MAX_PER_PRODUCT, maxAllowed, quantityOf, addItem, setQuantity, removeItem, itemCount, basketLines, basketTotal, revalidate };
}
