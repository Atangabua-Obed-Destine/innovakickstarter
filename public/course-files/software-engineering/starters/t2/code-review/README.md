# Interview practice: code review exercise

Fru, a teammate, opened pull request #17. Your job: review it the way GitLab and Google ask reviewers to.
Put this folder in your course repository as `ch11-job-ready/code-review/`.

## Files
| File | What it is |
|---|---|
| `PR.md` | The ticket and Fru's description. Read it first: what should the code do? |
| `pr-17.diff` | The change, as GitHub shows it. |
| `pr-17/orders/` | The code after the change, so you can run it. |
| `pr-17/my-bug.test.js` | **Your work:** one test that proves one bug. |
| `REVIEW.md` | **Your work:** your review comments and your decision. |

## Steps
1. Read `PR.md`, then `pr-17.diff`, line by line. Note every question you have.
2. In `pr-17/`, run `node --test`. Fru's 3 tests pass and your TODO test fails. Ask yourself: why do weak tests pass?
3. Write your comments in `REVIEW.md`, most important first, with a label for each.
4. Write `my-bug.test.js` so it fails on Fru's code, and explain it in `REVIEW.md`.
