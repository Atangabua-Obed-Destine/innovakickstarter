# Props: Passing Data Down

Make the product card reusable: it now receives its product and branch as props.

## Your TODOs
| TODO | File | What to do |
|---|---|---|
| 1 | `src/components/StockBadge.jsx` | A badge that receives `quantity` and uses `stockStatus` and `stockText`. |
| 2 | `src/components/ProductCard.jsx` | A card that receives `product` and `branch` (default `'NKW'`). |
| 3 | `src/components/Panel.jsx` | A box with a `title` that shows its `children`. |
| 4 | `src/App.jsx` | Three cards (Rice 50kg, Maggi Cubes, Detergent 1kg) and the branch details in a Panel. |

## Done when
- [ ] `npm test` shows 11 passing tests.
- [ ] Changing `BRANCH` in `App.jsx` to `'CAV'` changes the badges, and nothing else needs editing.

## Run it
You need Node.js 22 or newer (`node -v`).
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch07-react/props/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first React project you install downloads about 21 MB. `--prefer-offline` re-uses what npm already has, so later projects cost almost no data.
3. `npm run dev` starts the page at http://localhost:5173 (Ctrl+C stops it).
4. `npm test` runs the self-check tests. They fail at first: that is expected.
