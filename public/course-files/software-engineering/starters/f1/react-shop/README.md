# Project: Nkwen Shop in React

Rebuild the Chapter 4 and 5 catalogue and basket as a React app that reads the live Nkwen Traders API.

## Bring your Chapter 4 files
Replace the three placeholders in `src/lib/` with your own files from `ch04-interactive/nkwen-shop/`: `basket.js`, `catalogue.js` and `storage.js`. In each one, delete the last line (`if (typeof module !== 'undefined') ...`) and add the `export { ... };` line shown in the placeholder. `src/lib/format.js` is ready (the same rules).

## What is ready
- `src/api/nkwen.js`: calls the API. `src/api/useCatalogue.js`: a hook that loads products, open branches and categories, with `status`, `retry` and a practice mode.
- `src/test/fakeApi.js` and `src/shop.test.jsx`: the self-check, with a pretend API, so it runs offline.

## Your TODOs
`src/App.jsx` (TODO 1 to 8) and every file in `src/components/` (TODO 9 to 15). Copy your components from the lessons and adapt them.

## User stories (the reviewer checks every one)
1. Products, branches and categories come from the live API. While they load I see "Loading products...".
2. If the API fails (Practice mode: Server error), I see an alert and a Try again button that works.
3. Search filters as I type, ignoring upper and lower case and spaces. Clearing it shows all 20.
4. The category filter works together with search.
5. When nothing matches, a message says so and repeats my words.
6. Each card shows the name, category, price with unit (`36,005 FCFA / bag`) and the stock badge for my branch. Add is disabled when that branch has none.
7. "Collect at" lists only the open branches (no Limbe).
8. I can add, increase, decrease and remove. Never more than the branch stock or 10 of one product; the message says when I reach the limit.
9. Line totals, the basket total and the header count are always right.
10. My basket and branch survive a refresh. Broken saved data gives an empty basket, not a crash.
11. Changing branch fits the basket to the new stock, and the message says exactly what changed.
12. It works with the keyboard alone; messages appear in the `role="status"` line; no component uses `document.` or `innerHTML`; `npm test` passes.

## What the tests look for
The tests find things the way a screen reader user would, so keep these words: the labels "Collect at", "Search", "Category" and "Practice mode"; a list named "Products" of `<article>` cards with the name in an `<h3>`; Add buttons whose name ends with the product name; a basket `<aside>` titled "Your basket" with buttons named "One more Rice 50kg", "One less Rice 50kg" and "Remove Rice 50kg"; "Basket: N items" in the header.

## Done when
- [ ] `npm test` shows 15 passing tests and `npm run build` works.
- [ ] You ticked all 12 user stories in the browser, with the real API.

## Run it
You need Node.js 22 or newer (`node -v`).
1. Create a new GitHub repository `nkwen-react-shop`, clone it, and copy these files into it.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first React project you install downloads about 21 MB. `--prefer-offline` re-uses what npm already has, so later projects cost almost no data.
3. `npm run dev` starts the page at http://localhost:5173 (Ctrl+C stops it).
4. `npm test` runs the self-check tests. They fail at first: that is expected.
