// Try your functions with different inputs:  node try.js
// require() loads the functions that pricing.js exports.
const { lineTotal, basketSubtotal, wholesaleDiscount, vatIncluded, amountToPay } = require('./pricing.js');

console.log('3 loaves of bread:', lineTotal(600, 3), 'FCFA');

// TODO: call each function at least three times with different inputs.
//       Include an "edge" input each time, for example 0, or exactly 50,000.
//       Before you run, write the answer you expect in a comment.
