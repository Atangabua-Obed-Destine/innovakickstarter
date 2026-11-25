// async-basics.js: your work for "Async JavaScript: Promises & async/await".
// Fill in each TODO. Run the self-check tests with:  node --test
const { getProduct } = require('./slow-shop.js');

// 1. wait(ms): return a Promise that resolves (with no value) after `ms` milliseconds.
//    Hint: new Promise((resolve) => setTimeout(resolve, ms))
function wait(ms) {
  // TODO
}

// 2. priceOf(id): an async function that returns the product's price_fcfa.
//    Use `await getProduct(id)`.
async function priceOf(id) {
  // TODO
}

// 3. priceOrNull(id): like priceOf, but if getProduct rejects (unknown id),
//    catch the error with try/catch and return null instead of crashing.
async function priceOrNull(id) {
  // TODO
}

// 4. basketTotalOneByOne(items): items look like [{ product_id: 6, quantity: 2 }].
//    Use a for...of loop and `await getProduct(...)` for each item, one after another.
//    Return the total in FCFA (price_fcfa x quantity, added up). An empty basket is 0.
async function basketTotalOneByOne(items) {
  // TODO
}

// 5. basketTotal(items): the same answer, but ask for all products at the same time.
//    Hint: const products = await Promise.all(items.map((item) => getProduct(item.product_id)));
async function basketTotal(items) {
  // TODO
}

module.exports = { wait, priceOf, priceOrNull, basketTotalOneByOne, basketTotal };
