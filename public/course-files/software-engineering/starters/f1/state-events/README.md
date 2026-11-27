# State & Events

The cards get an Add button, and the page remembers the basket in state.

## Bring your Chapter 4 files (TODO 1)
`src/lib/basket.js` is a placeholder. Replace it with your own `basket.js` from Chapter 4 (`ch04-interactive/nkwen-shop/basket.js` in your repository). Then make it an ES module: delete its last line (`if (typeof module !== 'undefined') ...`) and add the `export { ... };` line shown at the top of the placeholder.

## Your other TODOs
| TODO | File | What to do |
|---|---|---|
| 2 | `src/components/ProductCard.jsx` | The Add button: `onAdd`, `inBasket`, the limit and the button words. |
| 3 to 7 | `src/App.jsx` | State for branch, basket and message; the Add, Empty and branch-change handlers; pass the new props. |
| 8 | `src/components/BasketSummary.jsx` | "Basket: 2 items, 1,200 FCFA", worked out from the basket every time. |

## Done when
- [ ] `npm test` shows 8 passing tests.
- [ ] In the browser, Add, Empty the basket and Collect at all work, and the message line says what happened.

## Run it
You need Node.js 22 or newer (`node -v`).
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch07-react/state-events/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first React project you install downloads about 21 MB. `--prefer-offline` re-uses what npm already has, so later projects cost almost no data.
3. `npm run dev` starts the page at http://localhost:5173 (Ctrl+C stops it).
4. `npm test` runs the self-check tests. They fail at first: that is expected.
