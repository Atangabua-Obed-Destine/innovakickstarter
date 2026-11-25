# Async basics: waiting for a slow shop

Practice for the lesson **Async JavaScript: Promises & async/await** (Chapter 5).

Put this folder in your repository as `ch05-talking-to-servers/async-basics/`.

## Files
| File | What it is |
|---|---|
| `slow-shop.js` | A pretend server. `getProduct(id)` answers after 300 ms. Do not change it. |
| `async-basics.js` | **Your work.** Five functions with TODOs. |
| `async-basics.test.js` | Self-check tests. They check your functions agree; they do not give answers. |
| `run.js` | Tries your functions and times them. |
| `freeze-demo.html` | Open it in your browser to see why we never "busy wait". |

## Steps
1. Open `freeze-demo.html` in Chrome or Edge. Try both wait buttons while clicking **Count customers**.
2. In a terminal in this folder, run `node --test`. All 6 tests fail at first. That is normal.
3. Fill in the TODOs in `async-basics.js` one at a time, in order. Run `node --test` after each one.
4. When all 6 tests pass, run `node run.js` and note the two timings.
5. Commit and push: `git add .` then `git commit -m "Practise promises and async/await"` then `git push`.

Needs Node.js 22 or newer. No internet needed.
