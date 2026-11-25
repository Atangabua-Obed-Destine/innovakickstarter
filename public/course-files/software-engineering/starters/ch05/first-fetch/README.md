# First fetch: the Nkwen Traders API

Practice for the lesson **Your First Fetch** (Chapter 5).

Put this folder in your repository as `ch05-talking-to-servers/first-fetch/`.

The API lives at `https://innovakickstarter.com/api/course/nkwen`. Open that address in your browser to see the list of endpoints.

## Files
| File | What it is |
|---|---|
| `nkwen-api.js` | **Your work.** Four helper functions with TODOs. You will reuse this file later in the chapter. |
| `nkwen-api.test.js` | Self-check tests (they call the real API, so you need internet). |
| `first-fetch.js` | **Your work.** A script with four TODOs that prints real data. |
| `index.html` | A page with buttons that uses your `getProducts`. Use it with the DevTools Network tab. |

## Steps
1. Run `node --test` in this folder. The tests fail at first. That is normal.
2. Fill in `buildUrl` and run the tests again. Tests 1 and 2 should pass.
3. Fill in `getJson`, `getProducts` and `getBranches`. Run the tests until all 7 pass.
4. Fill in the four TODOs in `first-fetch.js`, then run `node first-fetch.js`.
5. Open `index.html` in Chrome or Edge with DevTools open on the **Network** tab. Click each button and look at the status codes.
6. Commit and push.

Needs Node.js 22 or newer (it has `fetch` built in). Check with `node --version`.
