# Event handling: price check and call-back form

## Files
- `validation.js`: the form rules (no page code). `validation.test.js` checks them.
- `events.js`: 4 TODOs that make the page react.
- `products.js`, `index.html`, `style.css`: ready.

## Steps
1. Run `node --test` in this folder. Write the 4 functions in `validation.js` until all 8 tests pass.
2. Open `index.html`. Do TODO 1 and TODO 2: the price check total changes as you type.
3. Do TODO 3: the character counter. Paste a long text to test it.
4. Do TODO 4: the submit listener. Try an empty form, a wrong phone number and a good form.
5. Use only the keyboard: Tab to each field, press Enter on the button. Where does the focus go when there is an error?

## Self-check
- [ ] `node --test` shows 8 passing tests.
- [ ] The total updates on every key press, and says "Enter a quantity" for 0 or an empty box.
- [ ] The counter turns red only when the message is too long, and goes back when it is short again.
- [ ] A wrong form shows a message under each wrong field and moves the focus to the first one. The page never reloads.
