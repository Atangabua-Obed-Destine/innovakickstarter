# Changing the page: the Commercial Avenue stock board

The product rows in `index.html` are empty. You will fill them from the real data, with the right price, stock badge and button state.

## Files
- `products.js`: the Nkwen Traders data. Do not edit.
- `format.js`: 3 small helpers to write. `format.test.js` checks them.
- `page.js`: 3 TODOs that change the page.
- `index.html`, `style.css`: ready. The badge styles `badge--in`, `badge--low` and `badge--out` are already in the CSS.

## Steps
1. Open a terminal in this folder and run `node --test`. All tests fail: that is expected.
2. Write the 3 functions in `format.js` until `node --test` shows 4 passing tests.
3. Open `index.html`. Do TODO 1 to TODO 3 in `page.js`. Refresh after each one.
4. In DevTools > Elements, click a badge. Check that it has exactly one `badge--` class.

## Self-check
- [ ] `node --test` shows 4 passing tests.
- [ ] Every row shows a name, a price like `36,005 FCFA`, a coloured badge and a button.
- [ ] Out-of-stock rows have a pale red card, and their button cannot be clicked.
- [ ] I used `textContent`, not `innerHTML`, for the data.
