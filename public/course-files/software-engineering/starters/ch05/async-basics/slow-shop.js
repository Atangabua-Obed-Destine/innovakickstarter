// slow-shop.js: a pretend "server" for practice. Do not change this file.
// Every function answers after a short wait, like a real server on a slow network.
// Each one returns a Promise: a value that is not ready yet.

const DELAY_MS = 300;

const PRODUCTS = [
  { id: 5, name: 'Rice 25kg', price_fcfa: 19055 },
  { id: 6, name: 'Rice 50kg', price_fcfa: 36005 },
  { id: 9, name: 'Sugar 1kg', price_fcfa: 760 },
  { id: 12, name: 'Matches (box)', price_fcfa: 100 },
  { id: 13, name: 'Soap (bar)', price_fcfa: 410 },
  { id: 15, name: 'Palm Oil 5L', price_fcfa: 6600 },
];

// getProduct(id) -> Promise that resolves to a product object,
// or rejects with an Error if there is no product with that id.
function getProduct(id) {
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      const product = PRODUCTS.find((p) => p.id === id);
      if (product) {
        resolve({ ...product });
      } else {
        reject(new Error(`No product with id ${id}.`));
      }
    }, DELAY_MS);
  });
}

module.exports = { getProduct, DELAY_MS, PRODUCTS };
