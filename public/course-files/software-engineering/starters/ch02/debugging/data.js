// Tuesday's sales from three branches, exported by the till app.
// The till app sometimes sends numbers as text. loadSales() is supposed to clean that.

const PRODUCTS = [
  { id: 1, name: 'Bread (loaf)', price_fcfa: 600 },
  { id: 3, name: 'Beans (Red)', price_fcfa: 930 },
  { id: 6, name: 'Rice 50kg', price_fcfa: 36005 },
  { id: 9, name: 'Sugar 1kg', price_fcfa: 760 },
  { id: 12, name: 'Matches (box)', price_fcfa: 100 },
  { id: 15, name: 'Palm Oil 5L', price_fcfa: 6600 },
];

const TILL_EXPORT = [
  { id: 101, branch: 'NKW', productId: 1, quantity: 4 },
  { id: 102, branch: 'ML4', productId: 9, quantity: 2 },
  { id: 103, branch: 'NKW', productId: 6, quantity: 1 },
  { id: 104, branch: 'CAV', productId: 12, quantity: 10 },
  { id: 105, branch: 'NKW', productId: 9, quantity: '3' },
  { id: 106, branch: 'ML4', productId: 1, quantity: 6 },
  { id: 107, branch: 'CAV', productId: 15, quantity: 1 },
  { id: 108, branch: 'NKW', productId: 1, quantity: 2 },
  { id: 109, branch: 'ML4', productId: 3, quantity: 5 },
  { id: 110, branch: 'CAV', productId: 1, quantity: 3 },
];

function loadProducts() {
  return PRODUCTS.map((p) => ({ ...p }));
}

// Returns a fresh copy of the sales, with every quantity as a number.
function loadSales() {
  return TILL_EXPORT.map((sale) => ({
    ...sale,
    quantity: sale.quantity, // make sure quantity is a number
  }));
}

module.exports = { loadProducts, loadSales };
