# Shared State: The Basket Everywhere

One basket that every component can read and change, saved in the browser, without passing props down the tree.

## Bring your typed files (TODO 1)
Replace `src/lib/basket.ts` and `src/lib/storage.ts` with yours from `typescript-essentials/src/lib/`.

## Your other TODOs
| TODO | File | What to do |
|---|---|---|
| 2 to 5 | `src/basket/BasketContext.tsx` | The reducer, the starting state, the provider (with an effect that saves), and `useBasket()`. |
| 6 | `src/components/Header.tsx` | "Basket: N items" from the shared basket. |

`App.tsx`, `ProductList.tsx`, `BasketPanel.tsx` and `BranchSelect.tsx` are ready: they already use `useBasket()`.

## Done when
- [ ] `npm test` shows 9 passing tests and `npm run typecheck` prints no errors.
- [ ] After a refresh, the basket and the branch are still there.

## Run it
You need Node.js 22.22 or newer (`node -v`): React Router 8 asks for it.
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch08-typescript/shared-basket/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first TypeScript project adds about 11 MB (the TypeScript compiler); later ones re-use npm's copy.
3. `npm run dev` starts the page at http://localhost:5173.
4. `npm test` runs the behaviour checks. `npm run typecheck` runs the type checker. Both fail at first.
