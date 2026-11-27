# Effects & Data Fetching

Write your own data-fetching hook and show live stock by branch in four clear states.

## Your TODOs
| TODO | File | What to do |
|---|---|---|
| 1 | `src/hooks/useApi.ts` | `useApi<T>(url)`: an effect that fetches, ignores old answers, and can retry. |
| 2 | `src/components/ProductsPanel.tsx` | Loading, error with Try again, empty (and Limbe "not open yet"), and ready. |

`src/App.tsx` uses your hook for the branches. `src/api/nkwen.ts` is ready. The tests use a pretend API (`src/test/fakeApi.ts`), so they run offline; the page itself uses the live API.

## Done when
- [ ] `npm test` shows 6 passing tests and `npm run typecheck` prints no errors.
- [ ] In the browser's Network tab, each branch change sends ONE request (two in development: see the lesson).

## Run it
You need Node.js 22.22 or newer (`node -v`): React Router 8 asks for it.
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch08-typescript/effects-fetching/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first TypeScript project adds about 11 MB (the TypeScript compiler); later ones re-use npm's copy.
3. `npm run dev` starts the page at http://localhost:5173.
4. `npm test` runs the behaviour checks. `npm run typecheck` runs the type checker. Both fail at first.
