# TypeScript Essentials

Describe Nkwen Traders' data with types, convert your Chapter 4 rules to TypeScript, check outside data, and let the type checker find four bugs.

## Your TODOs
| TODO | File | What to do |
|---|---|---|
| 1 | `src/types.ts` | Replace each `unknown` and finish the interfaces. `src/types.check.ts` tests them: do not edit it. |
| 2 | `src/lib/format.ts` | Add parameter and return types. |
| 3 | `src/lib/basket.ts`, `src/lib/storage.ts` | Paste in your Chapter 4 `basket.js` and `storage.js` (as ES modules: `export function ...`) and add types until the checker is happy. |
| 4 | `src/lib/parse.ts` | `isBranchCode` (a type guard) and `parseProduct(data: unknown)`. |
| 5 | `src/lib/report.ts` | Delete `// @ts-nocheck`, read the errors, fix the four bugs, fill in `TYPEBUGS.md`. |

`npm run dev` shows the restock report from `report.ts`: look at it before and after TODO 5.

## Done when
- [ ] `npm run typecheck` prints no errors.
- [ ] `npm test` shows 9 passing tests.
- [ ] `TYPEBUGS.md` has four rows with the real error messages.

## Run it
You need Node.js 22.22 or newer (`node -v`): React Router 8 asks for it.
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch08-typescript/typescript-essentials/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first TypeScript project adds about 11 MB (the TypeScript compiler); later ones re-use npm's copy.
3. `npm run dev` starts the page at http://localhost:5173.
4. `npm test` runs the behaviour checks. `npm run typecheck` runs the type checker. Both fail at first.
