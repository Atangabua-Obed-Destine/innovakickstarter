// A few Nkwen products, with their shelf prices (VAT already included).
const PRODUCTS = [
  { id: 5, name: 'Rice 25kg', unit: 'bag', price_fcfa: 19055 },
  { id: 6, name: 'Rice 50kg', unit: 'bag', price_fcfa: 36005 },
  { id: 9, name: 'Sugar 1kg', unit: 'bag', price_fcfa: 760 },
  { id: 13, name: 'Soap (bar)', unit: 'bar', price_fcfa: 410 },
  { id: 15, name: 'Palm Oil 5L', unit: 'jerrycan', price_fcfa: 6600 },
];

function findProduct(id) {
  const product = PRODUCTS.find((p) => p.id === id);
  if (!product) throw new Error(`No product with id ${id}`);
  return product;
}

module.exports = { findProduct };
