// This function was written by an AI assistant from this prompt:
//   "Write a JavaScript function that adds up a Nkwen Traders basket.
//    Each item has a name, a unit price in FCFA and a quantity.
//    Baskets worth 50,000 FCFA or more get 10% off.
//    Round the answer to the nearest franc."
//
// Your job: explain every line, find the hidden bug, prove it with a test, fix it.

function basketTotal(items) {
  let total = 0;
  for (const item of items) {
    total = total + item.price * item.quantity;
  }
  if (total > 50000) {
    total = total - total * 0.1;
  }
  return Math.round(total);
}

// This line lets other files (like the tests) use basketTotal.
module.exports = { basketTotal };
