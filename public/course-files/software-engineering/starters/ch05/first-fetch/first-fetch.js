// first-fetch.js: your first real requests to the Nkwen Traders API.
// Run:  node first-fetch.js
const { getProducts, getBranches } = require('./nkwen-api.js');

async function main() {
  // TODO 1: get all products and print how many there are.
  //         Example output: "The API has N products."

  // TODO 2: get the Grains products sorted by price, cheapest first
  //         ({ category: 'Grains', sort: 'price' }), and print the 3 cheapest
  //         as "name - price FCFA".

  // TODO 3: get the branches and print one line per branch: code, name and city.
  //         Add "(not open yet)" when the branch status is "planned".

  // TODO 4: ask for products with { fail: 503 } inside try { } catch (error) { }.
  //         In the catch, print a friendly message for a shop manager, plus error.message.
}

main();
