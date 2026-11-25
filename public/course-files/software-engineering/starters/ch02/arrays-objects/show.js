// Print a catalogue for one branch:  node show.js
// require() can load a JSON file straight into a JavaScript object.
const data = require('./nkwen.json');
const { stockValue, outOfStock } = require('./catalogue.js');

const BRANCH = 'NKW';
const products = data.products;

console.log(`Nkwen Traders catalogue for ${BRANCH} (${products.length} products)`);

// TODO 1: loop over products and print one line per product, for example:
//         Rice 50kg | Grains | 36005 FCFA | 42 in stock
// TODO 2: print the stock value of the branch with stockValue().
// TODO 3: print the products that are out of stock there, with outOfStock().
// TODO 4: change BRANCH to another open branch and run again.
