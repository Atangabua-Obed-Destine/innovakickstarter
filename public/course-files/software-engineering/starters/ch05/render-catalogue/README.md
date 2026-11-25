# Rendering real data: live products page

Practice for the lesson **Rendering Real Data into the DOM** (Chapter 5).

Put this folder in your repository as `ch05-talking-to-servers/render-catalogue/`.

## Files
| File | What it is |
|---|---|
| `index.html`, `styles.css` | The page. You do not need to change them. |
| `format.js` | `formatFcfa`, `stockStatus` and `stockText` from Chapter 4. Use your own copy if you have it. |
| `view.js` | **Your work.** Two small functions that decide what to show. |
| `view.test.js` | Self-check tests for `view.js` (no internet needed). |
| `app.js` | **Your work.** Three TODOs (A, B, C) that update the page. |
| `nkwen-api.js` | **Not included.** Copy yours from `first-fetch/`. Every test in that folder must pass first. |

## Steps
1. Copy `nkwen-api.js` from your `first-fetch` folder into this folder.
2. Run `node --test`. Fill in `view.js` until all 3 tests pass.
3. Fill in TODO A, B and C in `app.js`.
4. Open `index.html` in Chrome or Edge. Check the four states:
   - **Loading:** choose *Practice mode: Slow network*. You should see the loading message for about 2.5 seconds.
   - **Error:** choose *Server error (500)*. You should see the error message and a *Try again* button.
   - **Empty:** choose the Limbe branch. You should see a "not open yet" message and no cards.
   - **Ready:** choose *Normal* and an open branch. You should see one card per product.
5. Commit, push, and turn on GitHub Pages for your repository if it is not on yet.
