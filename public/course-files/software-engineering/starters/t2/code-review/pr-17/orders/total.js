const { findProduct } = require('./catalogue');

// Prices always come from our catalogue, never from the request.
function lineTotal(item) {
  const product = findProduct(item.product_id);
  const price = item.price_fcfa ?? product.price_fcfa;
  return price * item.quantity;
}

function isWholesale(items) {
  const bags = items
    .filter((item) => findProduct(item.product_id).unit === 'bag')
    .reduce((sum, item) => sum + item.quantity, 0);
  return bags > 10;
}

function orderTotal(items, customer = {}) {
  items.sort((a, b) => a.product_id - b.product_id); // receipts list products by id
  const lines = items.map((item) => ({ ...item, line_total_fcfa: lineTotal(item) }));
  let total = lines.reduce((sum, line) => sum + line.line_total_fcfa, 0);

  if (isWholesale(items)) {
    console.log('wholesale order for', customer.name, customer.phone);
    total = total * 0.95;
  }

  const vat = Math.round(total * 0.1925);
  return { lines, total_fcfa: total + vat, vat_fcfa: vat };
}

module.exports = { orderTotal, isWholesale };
