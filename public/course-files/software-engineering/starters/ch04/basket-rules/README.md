# Problem Solving 104: the basket rules

Before the shop page can have a basket, the rules must be right. Mr. Asong agreed these rules with the branch managers:

1. A basket is a list of lines `{ id, qty }`, one line per product.
2. A customer may order at most **the stock at their branch**, and never more than **10** of one product online.
3. "Add" adds one unit, unless the limit is reached. Then nothing changes.
4. Typing a quantity sets it exactly. Not a whole number: nothing changes. 0 or less: the line is removed. Above the limit: it becomes the limit.
5. Totals are whole FCFA: price x quantity for each line, added up.
6. If the customer switches branch, every line must fit the new branch's stock. Lines that drop to 0 disappear, and the customer is told what changed.
7. No function changes the basket it receives. Each returns a new basket.

## Files
- `basket.js`: 7 functions to write (2 are ready as examples).
- `basket.test.js`: 9 self-checks, using the Bafoussam Centre and Mile 4 branches.
- `scenario.js`: a real customer's story from the Nkwen branch. You will run it.
- `products.js`: the real data. Do not edit.

## Steps
1. Run `node --test`. Every test fails: that is expected.
2. Write the functions in the order of the TODOs. Run `node --test` after each one.
3. Finish `scenario.js` with calls to your functions only. Run `node scenario.js`.
4. Answer the questions in "What to submit" on the activity page.

## Self-check
- [ ] `node --test` shows 9 passing tests.
- [ ] `node scenario.js` prints both parts, and I did not work any number out by hand.
- [ ] I checked one line total from the scenario with a calculator.
