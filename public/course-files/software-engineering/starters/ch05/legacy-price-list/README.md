# Problem Solving 105: the old price list

Put this folder in your repository as `ch05-talking-to-servers/legacy-price-list/`.

The old till system (2019) exported a price list. It is messy. See it at
`https://innovakickstarter.com/api/course/nkwen/legacy/price-list`.

## Files
| File | What it is |
|---|---|
| `clean.js` | **Your work.** Six small functions with TODOs. |
| `clean.test.js` | Self-check tests. Tests 1-6 use made-up rows; test 7 runs on the real list. |
| `run.js` | Prints a cleaning report from the real API. |
| `DECISIONS.md` | **Your work.** Four short answers about the choices you made. |
| `nkwen-api.js` | **Not included.** Copy yours from `first-fetch/`. |

## Steps
1. Copy `nkwen-api.js` from your `first-fetch` folder into this folder.
2. Open the legacy price list in your browser. Write down every problem you can see before you code.
3. Run `node --test`. Fill in `clean.js` one function at a time until all 7 tests pass.
4. Run `node run.js` and read the report.
5. Fill in `DECISIONS.md`.
6. Commit and push.
