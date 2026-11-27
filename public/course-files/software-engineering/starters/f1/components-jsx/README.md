# Components & JSX

Build the first Nkwen Traders page out of components.

## Your TODOs
| TODO | File | What to do |
|---|---|---|
| 1 | `src/components/BranchNotice.jsx` | Fix five JSX mistakes. The page cannot start until you do. |
| 2 | `src/components/Header.jsx` | The site header with a "Main" menu of three links. |
| 3 | `src/components/ProductCard.jsx` | The Rice 50kg card: name, category, price and unit. |
| 4 | `src/components/Footer.jsx` | The footer, with this year worked out by JavaScript. |

`src/App.jsx` puts the components together. `src/lib/format.js` has the Chapter 4 display rules. `src/data/nkwen.js` is the shop's data.

## Done when
- [ ] `npm test` shows 11 passing tests.
- [ ] The page in the browser shows the header, the notice, the card and the footer, with no red errors in the Console.

## Run it
You need Node.js 22 or newer (`node -v`).
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch07-react/components-jsx/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first React project you install downloads about 21 MB. `--prefer-offline` re-uses what npm already has, so later projects cost almost no data.
3. `npm run dev` starts the page at http://localhost:5173 (Ctrl+C stops it).
4. `npm test` runs the self-check tests. They fail at first: that is expected.
