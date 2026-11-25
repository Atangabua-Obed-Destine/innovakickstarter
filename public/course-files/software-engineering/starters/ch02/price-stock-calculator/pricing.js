// Prices, discounts and VAT. Reuse your work from "Functions & Reusability".
// Money is always a whole number of francs.

// 1. unit price x quantity
function lineTotal(unitPrice, quantity) {
  // TODO
}

// 2. 10% off (rounded) when the subtotal is 50,000 FCFA or more, otherwise 0.
function wholesaleDiscount(subtotal) {
  // TODO
}

// 3. The VAT inside a price that already includes 19.25% VAT (rounded).
function vatIncluded(priceWithVat) {
  // TODO
}

// 4. Price a basket of items like [{ productId: 6, quantity: 2 }].
//    Look each product up in `products` (from nkwen.json).
//    If a productId does not exist, throw new Error(`Unknown product ${id}`).
//    Return an object shaped like this:
//    {
//      lines: [{ name, quantity, unitPrice, lineTotal }],  // one per item, same order
//      subtotal,   // sum of the line totals
//      discount,   // wholesaleDiscount(subtotal)
//      total,      // subtotal - discount
//      vat,        // vatIncluded(total)
//    }
function priceBasket(items, products) {
  // TODO
}

module.exports = { lineTotal, wholesaleDiscount, vatIncluded, priceBasket };
