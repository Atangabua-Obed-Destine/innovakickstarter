// The customer's story from Mrs. Enow Patience (Nkwen branch). Run:  node scenario.js
// Use ONLY the functions in basket.js. Do not work anything out by hand.
const { NKWEN } = require('./products.js');
const B = require('./basket.js');
const product = id => NKWEN.products.find(p => p.id === id);
const fcfa = n => n.toLocaleString('en-US') + ' FCFA';

let basket = [];
let branch = 'NKW';

// Part A: at the Nkwen branch (NKW) the customer:
// 1. clicks "Add" on Rice 50kg (id 6) two times
// 2. clicks "Add" on Maggi Cubes (pack) (id 7) five times
// 3. clicks "Add" on Detergent 1kg (id 11) once
// 4. types 25 in the quantity box of Matches (box) (id 12)
// 5. types 4 in the quantity box of Sugar 1kg (id 9)
// 6. types 2.5 in the quantity box of Palm Oil 5L (id 15), by mistake
// TODO: write each step with addItem or setQuantity, for example:
//   basket = B.addItem(basket, product(6), branch);

console.log('Part A basket:', B.basketLines(basket, NKWEN.products));
console.log('Part A items:', B.itemCount(basket), '| total:', fcfa(B.basketTotal(basket, NKWEN.products)));

// Part B: the customer decides to collect at Commercial Avenue (CAV) instead.
// TODO: use B.revalidate, keep the new basket, and print the changes.

console.log('Part B changes:', []);
console.log('Part B items:', B.itemCount(basket), '| total:', fcfa(B.basketTotal(basket, NKWEN.products)));
