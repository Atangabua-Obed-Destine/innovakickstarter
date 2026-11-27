// Interview practice: arrays, strings, hash maps and two pointers.
// Every problem uses Nkwen Traders data. Run the self-checks: node --test

// 1. HASH MAP. A wholesale customer has exactly `budget` FCFA and wants TWO
// different products whose prices add up to exactly the budget.
// Return the two positions [i, j] with i < j, or null if no pair exists.
function pairForBudget(prices, budget) {
  // TODO: one loop and a Map. For each price, ask: have I already seen budget - price?
  throw new Error('TODO: write pairForBudget()');
}

// 2. HASH MAP (counting). Today's visits, one name per visit, in time order.
// Return the FIRST customer who came only once today, or null.
function firstOneTimeCustomer(visits) {
  // TODO: count every name first (a Map), then walk the visits again in order.
  throw new Error('TODO: write firstOneTimeCustomer()');
}

// 3. HASH MAP (grouping). Count products per category:
// [{name, category}, ...] -> { Grains: 4, Oils: 3, ... }
function countByCategory(products) {
  // TODO: one loop; an object or a Map from category to count.
  throw new Error('TODO: write countByCategory()');
}

// 4. TWO POINTERS (strings). Delivery codes are read over the phone, so
// staff check them both ways. Is the code the same forwards and backwards,
// ignoring spaces, dashes and letter case?  "NK-7-kn" -> true
function isMirrorCode(code) {
  // TODO: clean the code (lower case, only letters and digits), then move a
  // left pointer and a right pointer towards the middle.
  throw new Error('TODO: write isMirrorCode()');
}

// 5. TWO POINTERS (merging). Two branches send their price lists, each
// already sorted from cheapest to dearest. Return ONE sorted list.
// Do not use .sort(): walk both lists at once.
function mergeSorted(a, b) {
  // TODO: one pointer in each list. Push the smaller value, move that pointer.
  // When one list is finished, copy the rest of the other.
  throw new Error('TODO: write mergeSorted()');
}

// 6. SLIDING WINDOW (two pointers that move together). Daily sales in FCFA.
// Return the best total over any `days` days in a row, or null if there are
// fewer than `days` days.
function bestRun(dailySales, days) {
  // TODO: add up the first `days` values, then slide the window one day at a
  // time: add the new day, take away the day that left.
  throw new Error('TODO: write bestRun()');
}

module.exports = { pairForBudget, firstOneTimeCustomer, countByCategory, isMirrorCode, mergeSorted, bestRun };
