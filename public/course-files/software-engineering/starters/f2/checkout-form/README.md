# Forms & Validation for Checkout

A checkout form that checks the answers before sending, explains every server answer, and a simulated mobile-money payment screen. No internet needed: a "Server answer (practice)" drop-down decides what the pretend server says.

## Your TODOs
| TODO | File | What to do |
|---|---|---|
| 1 and 2 | `src/checkout/validate.ts` | `validateCheckout` and `serverErrorsToFields`. |
| 3 to 6 | `src/checkout/CheckoutForm.tsx` | Submit (validate, focus, send, handle all four results), error wiring, the phone field only for mobile money, and the button while sending. |
| 7 | `src/checkout/PaymentScreen.tsx` | The simulated payment: request, wait for the phone, check the status, paid or failed. |

`src/checkout/momo.ts` (the simulation) and `src/App.tsx` are ready.

**Never** add a PIN field. A real customer types the PIN on their own phone, never on a website.

## Done when
- [ ] `npm test` shows 15 passing tests and `npm run typecheck` prints no errors.
- [ ] You tried all four server answers and all three phone answers in the browser.

## Run it
You need Node.js 22.22 or newer (`node -v`): React Router 8 asks for it.
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch08-typescript/checkout-form/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first TypeScript project adds about 11 MB (the TypeScript compiler); later ones re-use npm's copy.
3. `npm run dev` starts the page at http://localhost:5173.
4. `npm test` runs the behaviour checks. `npm run typecheck` runs the type checker. Both fail at first.
