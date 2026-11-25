// Programming Logic: predict, then run.
// For each block, write your prediction on the "My prediction" line
// BEFORE you run the file. Then run:  node predict.js
// Wrong predictions are fine. Explaining why is the real exercise.

// 1. Variables and numbers
let pricePerLoaf = 600;
let loaves = 3;
let total = pricePerLoaf * loaves;
console.log('1:', total);
// My prediction for 1:

// 2. Text and numbers
let quantityTyped = '5'; // text typed into a form, not a number
console.log('2:', quantityTyped + 3, quantityTyped * 3);
// My prediction for 2:

// 3. if / else if / else
let stock = 0;
if (stock > 20) {
  console.log('3: plenty');
} else if (stock > 0) {
  console.log('3: running low');
} else {
  console.log('3: empty');
}
// My prediction for 3:

// 4. A loop that adds up
let weekTotal = 0;
for (let day = 1; day <= 3; day++) {
  weekTotal = weekTotal + 1000 * day;
}
console.log('4:', weekTotal);
// My prediction for 4:

// 5. True or false
const branch = 'LMB';
const isOpen = branch === 'NKW' || branch === 'ML4' || branch === 'CAV' || branch === 'BAF';
console.log('5:', isOpen, !isOpen);
// My prediction for 5:

// 6. A while loop that sells 2 bags at a time
let bags = 5;
let sold = 0;
while (bags > 0) {
  bags = bags - 2;
  sold = sold + 2;
}
console.log('6:', bags, sold);
// My prediction for 6:
