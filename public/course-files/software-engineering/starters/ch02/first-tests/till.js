// Till functions used at every Nkwen Traders branch.
// This code works (we think). Your job is to PROVE it with tests.

// The price of one receipt line. Quantity must be a whole number of at least 1.
function lineTotal(unitPrice, quantity) {
  if (!Number.isInteger(quantity) || quantity < 1) {
    throw new Error('Quantity must be a whole number of at least 1');
  }
  return unitPrice * quantity;
}

// 10% off (rounded to the franc) when the subtotal is 50,000 FCFA or more.
function wholesaleDiscount(subtotal) {
  if (subtotal >= 50000) {
    return Math.round(subtotal * 0.1);
  }
  return 0;
}

// The stock left after selling some units. You cannot sell what you do not have.
function sell(stock, quantity) {
  if (quantity > stock) {
    throw new Error(`Not enough stock: ${stock} left, ${quantity} asked`);
  }
  return stock - quantity;
}

module.exports = { lineTotal, wholesaleDiscount, sell };
