import { NKWEN } from './nkwen.js';

// Pretends to fetch the products from the API: the answer arrives after 300 ms.
// (No internet needed for this challenge.)
export function loadProducts() {
  return new Promise((resolve) => {
    setTimeout(() => resolve(NKWEN.products), 300);
  });
}
