# Typing React Components

Give every component's props a type, type the events, and tell `useState` what it holds.

## Your TODOs
| TODO | File | What to do |
|---|---|---|
| 1 | `src/lib/branches.ts` | `isOpenBranchCode`, a type guard. |
| 2 to 6 | `src/components/*.tsx` | A props interface for StockBadge, Panel, ProductCard, QuantityPicker and BranchSelect. Fix the bug TypeScript then shows you in QuantityPicker. |
| 7 | `src/App.tsx` | `useState<OpenBranchCode>(...)` and `useState<Basket>(...)`. |

`src/props.check.tsx` tests your props with the type checker: do not edit it.

## Done when
- [ ] `npm run typecheck` prints no errors.
- [ ] `npm test` shows 4 passing tests.

## Run it
You need Node.js 22.22 or newer (`node -v`): React Router 8 asks for it.
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch08-typescript/typed-components/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first TypeScript project adds about 11 MB (the TypeScript compiler); later ones re-use npm's copy.
3. `npm run dev` starts the page at http://localhost:5173.
4. `npm test` runs the behaviour checks. `npm run typecheck` runs the type checker. Both fail at first.
