# Project: Live Catalogue from the Nkwen API

Chapter 5 project. Put this folder in your repository as `ch05-talking-to-servers/live-catalogue/`
and publish it with GitHub Pages.

## What it must do
1. Load live products, categories and branches from the Nkwen Traders API.
2. Show all four states clearly: loading, error (with *Try again*), empty, ready.
3. Filter through **query parameters**: search, category, branch and sort are sent to the API.
4. Keep a basket (it survives a refresh) and send a **test order** to `POST /orders`.
5. Show the three answers clearly: **201** (order checked), **422** (fix the form), **409** (not enough stock).

## Files
| File | What it is |
|---|---|
| `index.html`, `styles.css` | A ready page. You may use your Chapter 4 page instead. |
| `catalogue.js` | **Your work.** Six rules for filters, basket and order results. |
| `catalogue.test.js` | Self-checks for `catalogue.js` (no internet). |
| `api.test.js` | Self-checks against the real API. They need `postOrder` in your `nkwen-api.js`. |
| `app.js` | **Your work.** Six TODOs that connect everything to the page. |
| `nkwen-api.js`, `format.js`, `view.js` | **Not included.** Copy yours from `render-catalogue/`, then add `postOrder` to `nkwen-api.js`. |

## How to see each order answer
- **201:** a normal order with your name.
- **422:** type a one-letter name, such as `A`.
- **409:** browse the Mile 4 branch, add 10 bags of Rice 25kg, then choose *Collect at branch: Nkwen* and send.

Run `node --test` until all 10 tests pass (5 in `catalogue.test.js`, 5 in `api.test.js`).
