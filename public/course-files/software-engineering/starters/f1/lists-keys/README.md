# Lists, Keys & Conditional Rendering

Draw every product from data, give each one a key, and show the right thing when nothing matches.

## Bring your Chapter 4 file (TODO 1)
Replace `src/lib/catalogue.js` with your own `catalogue.js` from your Chapter 4 project, and make it an ES module (see the comment at the top of the placeholder).

## Your other TODOs
| TODO | File | What to do |
|---|---|---|
| 2 | `src/components/ProductList.jsx` | `map` the products into `<li key={p.id}>` items, or a helpful message when there are none. |
| 3 | `src/components/CategoryChips.jsx` | A second list: one chip per category that has products, with its count. |
| 4 | `src/components/ResultCount.jsx` | Fix the famous "0" bug. |

`src/App.jsx` and `src/components/ProductCard.jsx` are ready. Read them.

## Done when
- [ ] `npm test` shows 9 passing tests.
- [ ] You tried the key experiment in the lesson (step 7) and wrote down what you saw.

## Run it
You need Node.js 22 or newer (`node -v`).
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch07-react/lists-keys/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first React project you install downloads about 21 MB. `--prefer-offline` re-uses what npm already has, so later projects cost almost no data.
3. `npm run dev` starts the page at http://localhost:5173 (Ctrl+C stops it).
4. `npm test` runs the self-check tests. They fail at first: that is expected.
