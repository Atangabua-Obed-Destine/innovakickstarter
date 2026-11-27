# Routing: Pages in a Single-Page App

Give the shop real addresses: `/`, `/products/6`, `/branches`, and a kind "not found" page.

## Your TODOs
| TODO | File | What to do |
|---|---|---|
| 1 | `src/main.tsx` | Wrap the app in `<BrowserRouter>`. |
| 2 | `src/App.tsx` | The route table. |
| 3 | `src/components/Layout.tsx` | Header, menu with NavLinks, and `<Outlet />`. |
| 4 and 6 | `src/pages/ShopPage.tsx` | Product links, and the category kept in the address (`/?category=Oils`). |
| 5 | `src/pages/ProductPage.tsx` | One product, read from `useParams()`, or "Product not found". |
| 7 | `src/pages/NotFoundPage.tsx` | "Page not found". |

This lesson uses the local data file, so it needs no internet.

## Done when
- [ ] `npm test` shows 8 passing tests and `npm run typecheck` prints no errors.
- [ ] The browser's Back and Forward buttons move between pages, and a refresh keeps you on the same page.

## Run it
You need Node.js 22.22 or newer (`node -v`): React Router 8 asks for it.
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch08-typescript/routing/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first TypeScript project adds about 11 MB (the TypeScript compiler); later ones re-use npm's copy.
3. `npm run dev` starts the page at http://localhost:5173.
4. `npm test` runs the behaviour checks. `npm run typecheck` runs the type checker. Both fail at first.
