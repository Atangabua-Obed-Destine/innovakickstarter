// Pricing functions for the Nkwen Traders till.
// Every function RETURNS its answer. None of them prints.
// Run the checks with:  node --test

// Cameroon's standard VAT rate is 19.25%. Our shelf prices already include it.
const VAT_RATE_PERCENT = 19.25;
const WHOLESALE_THRESHOLD = 50000; // FCFA
const WHOLESALE_PERCENT = 10;

// 1. The price of one line on a receipt: unit price x quantity.
function lineTotal(unitPrice, quantity) {
  // TODO
}

// 2. The subtotal of a basket. A basket is an array of lines like
//    { name: 'Sugar 1kg', price: 760, quantity: 2 }.
//    Reuse lineTotal() inside your loop.
function basketSubtotal(lines) {
  // TODO
}

// 3. The wholesale discount in FCFA: 10% of the subtotal when the subtotal
//    is 50,000 FCFA or more, otherwise 0. Round to the nearest franc.
function wholesaleDiscount(subtotal) {
  // TODO
}

// 4. How much of a shelf price is VAT. The price already includes VAT,
//    so VAT part = price x 19.25 / 119.25. Round to the nearest franc.
function vatIncluded(priceWithVat) {
  // TODO
}

// 5. What the customer pays: the subtotal minus the wholesale discount.
//    Reuse basketSubtotal() and wholesaleDiscount(). Do not copy their code.
function amountToPay(lines) {
  // TODO
}

module.exports = { lineTotal, basketSubtotal, wholesaleDiscount, vatIncluded, amountToPay };
