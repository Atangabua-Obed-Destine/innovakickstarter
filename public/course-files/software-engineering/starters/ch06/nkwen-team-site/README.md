# Nkwen Traders team site

Starter for the Chapter 6 project **Team Contribution to the Nkwen Repository**. You work in pairs.

## Run it
- Open `index.html` in a browser.
- Run the tests from this folder: `node --test` (2 tests pass at the start).

## The two feature cards
Each partner builds ONE feature on their own branch and opens a pull request. Decide who is A and who is B.

### Feature A: Branch finder (`feature/branch-finder`)
Customers ask "Which shop is near me, and who is the manager?"
- Add a page `branches.html` that lists every branch: name, city, manager and the year it opened.
- The planned Limbe branch must say "Opening 2027" and must not look like an open shop.
- Add two functions to the END of `js/shop.js`: `openBranches(branches)` (only open ones) and `branchLabel(branch)` (the text for one branch). Export them.
- Add tests for both functions to the END of `tests/shop.test.js`.
- Add a "Branches" link to the navigation on **every** page, after "Products".

### Feature B: Product search (`feature/product-search`)
Customers ask "Do you sell palm oil, and how much is it?"
- On `products.html`, add a search box (with a label). Typing filters the list by product name, ignoring upper/lower case.
- When nothing matches, show "No products match ..." instead of an empty list.
- Add a function to the END of `js/shop.js`: `searchProducts(products, text)`. Export it.
- Add tests to the END of `tests/shop.test.js` (include: empty text, different case, no match).
- Add a "Search" link to the navigation on **every** page, after "Products", that goes to `products.html#search`.

Both features change the same lines (the navigation, the end of `shop.js` and of the tests).
That is on purpose: the second pull request to merge will have a **merge conflict** to resolve together.
