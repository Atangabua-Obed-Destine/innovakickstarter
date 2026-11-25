// run.js: try your functions and time them.  Run:  node run.js
const { priceOf, priceOrNull, basketTotalOneByOne, basketTotal } = require('./async-basics.js');

async function main() {
  console.log('Asking the slow shop for the price of Palm Oil 5L (id 15)...');
  console.log('Price:', await priceOf(15), 'FCFA');
  console.log('Price of id 999 (does not exist):', await priceOrNull(999));

  const basket = [
    { product_id: 6, quantity: 2 },
    { product_id: 9, quantity: 5 },
    { product_id: 12, quantity: 10 },
    { product_id: 13, quantity: 4 },
  ];

  let start = Date.now();
  const total1 = await basketTotalOneByOne(basket);
  console.log(`One by one: ${total1} FCFA in ${Date.now() - start} ms`);

  start = Date.now();
  const total2 = await basketTotal(basket);
  console.log(`All at once: ${total2} FCFA in ${Date.now() - start} ms`);
}

main().catch((error) => console.error('Something went wrong:', error.message));
