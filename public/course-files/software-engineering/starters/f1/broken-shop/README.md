# Problem Solving F1: Fix the Broken Shop

A colleague built this shop in a hurry. Customers say it is broken. There are **5 bugs**. The tests in `src/shop.test.jsx` describe what the shop SHOULD do: they are correct, so do not change them.

## How to work
1. Run `npm run dev` and open the page. Open DevTools (F12): read the Console.
2. Run `npm test`. At first all 7 tests fail, because the first bug crashes the page.
3. Fix ONE bug at a time. After each fix, run `npm test` again and write a row in `BUGLOG.md`.
4. Use React DevTools (Components tab) to look at the props and state of `App` and `BasketLine` for at least two bugs.

Make the smallest fix you can. Do not rewrite whole components.

## Done when
- [ ] `npm test` shows 7 passing tests, and `src/shop.test.jsx` is unchanged.
- [ ] `BUGLOG.md` has five rows and the React rule behind each bug.

## Run it
You need Node.js 22 or newer (`node -v`).
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch07-react/broken-shop/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first React project you install downloads about 21 MB. `--prefer-offline` re-uses what npm already has, so later projects cost almost no data.
3. `npm run dev` starts the page at http://localhost:5173 (Ctrl+C stops it).
4. `npm test` runs the self-check tests. They fail at first: that is expected.
