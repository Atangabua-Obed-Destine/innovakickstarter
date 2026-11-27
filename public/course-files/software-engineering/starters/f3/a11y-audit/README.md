# Starter: Accessibility Audit (Chapter 9, Frontend)

The checkout page for Mrs. Mbah Clarisse, who uses a screen reader. It works with a mouse. It does not work for her.

```
npm install
npm test        # 6 accessibility tests: all fail at the start
npm run dev     # open the page and try it with the keyboard only
npm run build && npm run preview   # the built page, for Lighthouse
```

**Do not change the tests.** Change the components (`src/components/*.tsx`, `src/CheckoutPage.tsx`), `index.html` and `src/index.css` until all 6 pass.

| Test | What it checks |
|---|---|
| has no problems that axe can find | axe-core, the checker inside Lighthouse. The failure lists each rule and the HTML that breaks it. |
| every form field has a visible label | `getByLabelText` finds the name and phone fields. |
| the payment methods are one named group | a group called "Payment method" holds the radio buttons. |
| the remove buttons say what they remove | a button called "Remove Rice 50kg". |
| a keyboard user can Tab to "Add Sugar 1kg to basket" | Tab reaches it, Enter adds it. |
| a phone number error is announced and linked to the field | the error is read out, and the field points to it. |

Some problems no test here can find (jsdom cannot see colours or focus rings). Find them with Lighthouse, the keyboard and a screen reader, as the lesson explains.
