# Forms in React

A wholesale price request form: shops and restaurants that buy 10 units or more ask for a call back.

## Your TODOs
| TODO | File | What to do |
|---|---|---|
| 1 | `src/lib/validate.js` | `validateRequest(values)`: one message per wrong field, `{}` when all is well. |
| 2 to 6 | `src/components/WholesaleForm.jsx` | One state object for all the answers, one change handler, submit, errors under the fields, and the thank-you message. |

The business name and phone fields are already wired: copy their pattern.

## Done when
- [ ] `npm test` shows 9 passing tests.
- [ ] With only the keyboard you can fill in the form, see the errors, fix them and send it.

## Run it
You need Node.js 22 or newer (`node -v`).
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch07-react/forms/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first React project you install downloads about 21 MB. `--prefer-offline` re-uses what npm already has, so later projects cost almost no data.
3. `npm run dev` starts the page at http://localhost:5173 (Ctrl+C stops it).
4. `npm test` runs the self-check tests. They fail at first: that is expected.
