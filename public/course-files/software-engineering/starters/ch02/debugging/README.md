# Debugging Challenge (Chapter 2)

A colleague wrote helpers for Tuesday's branch report in a hurry. There are **5 bugs** across `report.js` and `data.js`. Some crash. Some give wrong numbers quietly.

- `data.js`: Tuesday's sales and products (and a cleaning step).
- `report.js`: the report helpers.
- `run.js`: prints the report. Use it with the debugger.
- `report.test.js`: what each helper **should** do. The tests are correct: do not change them.
- `BUGLOG.md`: your bug log. Fill in one row per bug.

## Steps

1. Copy this folder into your repository as `ch02-javascript/debugging/`.
2. Run `node run.js`. Read the whole error: the message, then the first stack trace line that points to `report.js` or `data.js`.
3. Run `node --test`. Write down which tests fail.
4. Fix **one bug at a time**. After each fix, run `node --test` again and fill in a row of `BUGLOG.md`.
5. For at least two bugs, use a **breakpoint** in VS Code (see the lesson) and write what you saw in the Variables panel.
6. Stop when every test passes and `node run.js` prints sensible numbers.
7. Commit and push.

Do not rewrite whole functions. The best fix is usually one small change.
