# Saving data in the browser: remember the basket

## Files
- `storage.js`: 5 functions to write. `storage.test.js` checks them with a fake `localStorage`.
- `demo.js`: the page code is ready. 5 small TODOs connect it to `storage.js`.
- `products.js`, `index.html`, `style.css`: ready.

## Steps
1. Run `node --test`. Write the functions in `storage.js` until all 8 tests pass.
2. Open `index.html` (Live Server is best). Add a few items, choose a branch, then refresh. Nothing is remembered yet.
3. Do the 5 TODOs in `demo.js`. Refresh again: the basket and branch should stay.
4. Open DevTools > **Application** > **Local storage**. Find `nkwen-basket` and read its value.
5. Break it on purpose: double-click the value, type `{oops`, press Enter, refresh the page. It must not crash.

## Self-check
- [ ] `node --test` shows 8 passing tests.
- [ ] After a refresh, the basket and branch are the same as before.
- [ ] Broken saved data gives an empty basket, not an error.
- [ ] "Empty the basket" also removes `nkwen-basket` from Local storage.
