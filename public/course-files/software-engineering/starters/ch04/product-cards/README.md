# Building and removing elements: product cards and a shopping list

`#product-list` is empty in the HTML. JavaScript builds every card from `products.js`.

## Files
- `cards.js`: 6 TODOs.
- `self-check.js`: click **Run the self-check** on the page. It clicks your buttons for you. Do not edit it.
- `products.js`, `index.html`, `style.css`: ready.

## Steps
1. Open `index.html`. Do TODO 1 and TODO 2, then TODO 6. You should see 20 cards.
2. Do TODO 5: the category list. Check that "Oils" shows only oils, and "All categories" shows all 20 again.
3. Do TODO 3 and TODO 4: the shopping list with Remove buttons.
4. Add 3 items, remove the middle one. In DevTools > Elements, check that no empty `<li>` is left behind.
5. Click **Run the self-check**.

## Self-check
- [ ] The self-check shows 7 PASS lines.
- [ ] Changing category many times never shows duplicate cards.
- [ ] "Remove" removes only its own item, even after many adds and removes.
