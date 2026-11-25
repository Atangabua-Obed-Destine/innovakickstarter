// catalogue.js: the catalogue and basket rules, with no page code, so Node can test them.
// Run the self-checks with:  node --test

// 1. filtersToParams({ search, category, branch, sort }): the query parameters for GET /products.
//    Trim the search text. Leave out any filter that is empty ('' or undefined).
//    { search: ' rice ', category: '', branch: 'NKW', sort: 'price' }
//      -> { search: 'rice', branch: 'NKW', sort: 'price' }
function filtersToParams(filters) {
  // TODO
}

// 2. addToBasket(basket, product, quantity = 1): return a NEW basket array (do not change the old one).
//    A basket line looks like { product_id, name, price_fcfa, quantity }.
//    If the product is already in the basket, add to its quantity instead of adding a second line.
function addToBasket(basket, product, quantity = 1) {
  // TODO
}

// 3. removeFromBasket(basket, productId): return a NEW basket without that product.
function removeFromBasket(basket, productId) {
  // TODO
}

// 4. basketTotal(basket): price_fcfa x quantity for every line, added up. Empty basket -> 0.
function basketTotal(basket) {
  // TODO
}

// 5. orderPayload(basket, { branch, customer_name, payment_method }): the JSON body for POST /orders.
//    { branch, customer_name (trimmed), payment_method, items: [{ product_id, quantity }, ...] }
function orderPayload(basket, details) {
  // TODO
}

// 6. describeOrderResult(status, body): turn the API's answer into a message for the customer.
//    Return { kind, title, lines } where lines is an array of strings:
//    201 -> kind 'success': title names the order id and the total (with thousands separators),
//           lines list each item ("2 x Rice 50kg = 72,010 FCFA") and say it was a test order.
//    422 -> kind 'invalid': title such as "Please fix the order", lines = every message in body.errors.
//    409 -> kind 'stock':   title = body.message, one line with what was asked for and what is available.
//    anything else -> kind 'error': a calm title that says to try again, lines = [].
function describeOrderResult(status, body) {
  // TODO
}

if (typeof module !== 'undefined') {
  module.exports = { filtersToParams, addToBasket, removeFromBasket, basketTotal, orderPayload, describeOrderResult };
}
