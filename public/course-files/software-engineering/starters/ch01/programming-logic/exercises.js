// Programming Logic: four small exercises for Nkwen Traders.
// Write your code under each TODO. Run often:  node exercises.js
// Check every printed number by hand (on paper or a calculator).

// Exercise 1: the price of a basket
// A customer buys 2 bags of Sugar 1kg (760 FCFA each),
// 5 bars of Soap (410 FCFA each) and 1 jerrycan of Palm Oil 5L (6,600 FCFA).
// TODO: store each price and quantity in a variable, then print:
//       Basket total: <total> FCFA


// Exercise 2: enough stock or not?
let quantity = 7;
let reorderLevel = 10;
// TODO: print "Order more" when quantity is at or below reorderLevel,
//       and "Enough" otherwise.
// Then change quantity to 10, then to 25, and run again each time.
// Write the three results in a comment.


// Exercise 3: a week of sales targets
// The Mile 4 branch wants to sell 20,000 FCFA on day 1,
// then 2,500 FCFA more than the day before, for 7 days.
// TODO: use a loop to print one line per day, like:
//       Day 1: target 20000 FCFA
// TODO: after the loop, print the total of the 7 targets.


// Exercise 4: a short product list
// An array is an ordered list of values, written in [ ].
// An object groups values under names (keys), written in { }.
const products = [
  { name: 'Bread (loaf)', price: 600 },
  { name: 'Matches (box)', price: 100 },
  { name: 'Rice 50kg', price: 36005 },
];
// TODO: loop over products (for...of) and print one line per product, like:
//       1. Bread (loaf) costs 600 FCFA
// TODO: count how many products cost more than 500 FCFA and print the count.
