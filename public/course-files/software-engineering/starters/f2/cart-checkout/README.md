# Project: Cart & Checkout

Turn the React shop into a typed, multi-page shop with one shared basket, a checkout that places real test orders with `POST /orders`, and a simulated mobile-money payment.

## Step 2: bring your parts
Every file that starts with `// BRING YOURS` comes from a lesson in this chapter. Replace each one with your own finished file:
- `src/lib/basket.ts`, `src/lib/storage.ts` (TypeScript Essentials)
- `src/components/StockBadge.tsx`, `ProductCard.tsx`, `QuantityPicker.tsx` (Typing React Components). In ProductCard, put a `<Link to={'/products/' + product.id}>` around the name.
- `src/hooks/useApi.ts` (Effects & Data Fetching)
- `src/basket/BasketContext.tsx` (Shared State)
- `src/checkout/validate.ts`, `CheckoutForm.tsx`, `PaymentScreen.tsx` (Forms & Validation for Checkout). Give CheckoutForm one more optional prop, `validate`, that defaults to `validateCheckout`.

## What is ready
`src/App.tsx` (routes and providers), `src/components/Layout.tsx`, `src/catalogue/CatalogueContext.tsx` (products and branches for every page), `src/api/nkwen.ts`, `src/api/orders.ts` (`postOrder`), `src/checkout/momo.ts`, and the self-check `src/project.test.tsx` with a pretend API.

## Your TODOs
The six pages in `src/pages/` (TODO 3 to 8).

## User stories (the reviewer checks every one)
1. It is TypeScript with `strict` on: `npm run typecheck` prints no errors and there is no `any`.
2. Pages: `/`, `/products/:id`, `/basket`, `/checkout`, `/pay`, `/done`, and "Page not found" for anything else. The menu marks the current page. Back and Forward work.
3. Products and branches come from the live API, with loading and error (Try again) states. Practice mode is in the footer.
4. One basket for the whole app: add from the shop or a product page, the menu shows the count everywhere, it survives a refresh, and changing branch fits it to the new stock with a message.
5. The basket page changes quantities (never above the limit), removes lines, shows the total, and links to checkout only when the basket is not empty.
6. Checkout checks the answers first: messages under the fields, focus on the first wrong one, a phone number only for mobile money. Nothing is sent while there are errors.
7. The order goes to `POST /orders` with the branch, the name, the payment method and `{ product_id, quantity }` for each line, never prices. It has an `Idempotency-Key` header that stays the same if I retry after a failure, and the button is disabled while sending.
8. 201: the basket empties. Cash or bank transfer: `/done` shows the order id, the server's lines and total, and says nothing was saved. Mobile money: `/pay`.
9. 422 (tick "Practice: skip the browser checks" and use the name `A`): the server's message appears under the right field.
10. 409 (browse Mile 4, add 10 Rice 25kg, then collect at Nkwen): the page says what I asked for and what is available, offers "Use 5 instead", and the order then goes through.
11. 500 or no network: a calm message, my basket is kept, and I can try again (with the same Idempotency-Key).
12. The payment screen is clearly a simulation: send the request, then approve, decline or time out. It never asks for a PIN.

## Done when
- [ ] `npm test` shows 13 passing tests, `npm run typecheck` prints no errors, and `npm run build` works.
- [ ] You ticked all 12 user stories in the browser, with the real API.
- [ ] Your pull request from `typescript-checkout` into `main` is merged.

## Run it
You need Node.js 22.22 or newer (`node -v`): React Router 8 asks for it.
1. In your `nkwen-react-shop` repository run `git switch -c typescript-checkout`, delete the old `src/`, `index.html`, `vite.config.js` and `package.json`, and copy these files in.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first TypeScript project adds about 11 MB (the TypeScript compiler); later ones re-use npm's copy.
3. `npm run dev` starts the page at http://localhost:5173.
4. `npm test` runs the behaviour checks. `npm run typecheck` runs the type checker. Both fail at first.
