// Restock Alerts for the Nkwen branch (stock counted on Monday morning).
// Read README.md first: it has Mrs. Enow Patience's rule.
// Run the report:  node restock.js
// Run the checks:  node --test

const BRANCH = 'Nkwen (NKW)';

// One object per product: its name, how many are on the shelf,
// and the reorder level (the number at which we must order more).
const products = [
  { name: 'Bread (loaf)', quantity: 37, reorderLevel: 10 },
  { name: 'Milk Powder 400g', quantity: 74, reorderLevel: 10 },
  { name: 'Beans (Red)', quantity: 21, reorderLevel: 12 },
  { name: 'Beans (White)', quantity: 58, reorderLevel: 12 },
  { name: 'Rice 25kg', quantity: 5, reorderLevel: 5 },
  { name: 'Rice 50kg', quantity: 42, reorderLevel: 5 },
  { name: 'Maggi Cubes (pack)', quantity: 3, reorderLevel: 15 },
  { name: 'Salt 1kg', quantity: 26, reorderLevel: 13 },
  { name: 'Sugar 1kg', quantity: 63, reorderLevel: 20 },
  { name: 'Tomato Paste (tin)', quantity: 10, reorderLevel: 12 },
  { name: 'Detergent 1kg', quantity: 0, reorderLevel: 8 },
  { name: 'Matches (box)', quantity: 84, reorderLevel: 30 },
  { name: 'Soap (bar)', quantity: 31, reorderLevel: 20 },
  { name: 'Palm Oil 1L', quantity: 3, reorderLevel: 10 },
  { name: 'Palm Oil 5L', quantity: 15, reorderLevel: 6 },
  { name: 'Vegetable Oil 5L', quantity: 52, reorderLevel: 6 },
  { name: 'Cassava (bag)', quantity: 89, reorderLevel: 10 },
  { name: 'Onions 1kg', quantity: 36, reorderLevel: 15 },
  { name: 'Plantain (bunch)', quantity: 73, reorderLevel: 10 },
  { name: 'Tomatoes 1kg', quantity: 20, reorderLevel: 15 },
];

// A function is a named block of code. You give it inputs (here: a quantity
// and a reorder level) and it gives back (returns) an answer.
// You will learn much more about functions in Chapter 2.
function stockStatus(quantity, reorderLevel) {
  // TODO: return 'OK', 'LOW' or 'RESTOCK' using the rule in README.md.
  // Think about the order of your checks (remember FizzBuzz).
  return 'TODO';
}

function printReport() {
  console.log('Restock alerts for ' + BRANCH);
  // TODO 1: loop over the products array.
  // TODO 2: for each product, call stockStatus() and print one line like:
  //         LOW - Example product: 15 left (reorder level 10)
  // TODO 3: count the OK, LOW and RESTOCK products while you loop.
  //         After the loop, print one summary line like:
  //         Summary: 1 OK, 2 LOW, 3 RESTOCK
}

// Leave the lines below as they are.
// They let the tests use your code, and they print the report
// when you type: node restock.js
module.exports = { stockStatus, products };
if (require.main === module) {
  printReport();
}
