# Problem Solving 102: Working With Data (Chapter 2)

- `sales.json`: every sale at the four open branches from Monday 16 to Sunday 22 November 2026.
  Each sale: `id`, `date` (local time), `branch`, `productId`, `quantity`, `payment`, `customer`.
- `nkwen.json`: products (with prices and today's stock per branch) and branches.
- `answers.js`: one function per question. Write your **approach comment first**, then the code.
- `answers.test.js`: self-checks that your answers agree with each other. Do not change it.

## Steps

1. Copy this folder into your repository as `ch02-javascript/working-with-data/`.
2. Open `sales.json`. Read three sales and say what each one means in a sentence.
3. For each question, write the approach comment, then the code. Run `node --test` often.
4. Write `main()` so that `node answers.js` prints every answer with a label.
5. Check one number by hand (for example one branch on one day).
6. Commit and push.
