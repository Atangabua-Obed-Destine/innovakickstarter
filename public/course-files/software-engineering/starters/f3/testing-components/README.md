# Starter: Testing React Components (Chapter 9, Frontend)

A tiny Nkwen shop (React + TypeScript + Vite) with **10 tests left for you to write**.

```
npm install
npm test          # runs every test once (Vitest)
npm run test:watch  # re-runs the tests each time you save
npm run build     # the production build must still work
```

At the start, `npm test` shows **5 passed and 10 failed**. Each failure says `TODO: write this test (README step N)`.

| File | What it is |
|---|---|
| `src/format.ts` + `format.test.ts` | The Chapter 4 helpers and worked example tests. |
| `src/components/ProductCard.tsx` + test | One product: name, price, stock badge, Add button. 4 TODO tests. |
| `src/components/BasketSummary.tsx` + test | The basket and its total. 3 TODO tests. |
| `src/components/ProductList.tsx` + test | Loads products from the API, with a search box. 3 TODO tests. |
| `src/test/fixtures.ts` | The 20 Nkwen products, so tests never need the network. |
| `src/test/mockFetch.ts` | Replaces `fetch` inside a test. |

## Steps
1. Install and run the tests: 5 passed, 10 failed.
2. Read `format.test.ts` and the first test in `ProductCard.test.tsx`.
3. ProductCard: the price test and the "Only N left" test.
4. ProductCard: the disabled button test and the `onAdd` test.
5. BasketSummary: the three tests.
6. ProductList: loading, then every product.
7. ProductList: an API error, then "Try again".
8. Test first: write the RICE test, watch it fail, then fix the search in `ProductList.tsx`.
9. Break `formatFcfa` on purpose and watch your tests catch it. Then undo.
10. Add the same tools to your own shop and write three tests there.

Do not change the components to make a test pass, except in step 8.
