# Problem Solving 108: Git History Detective

Mrs. Enow Patience (Nkwen branch) phoned head office: since last week the till charges the wrong price for **Sugar 1kg**.
Find the commit that broke the price list, prove it, and fix it **without rewriting shared history**.

Put this folder in your repository as `ch06-data-and-teamwork/git-detective/`.

## Files
| File | What it is |
|---|---|
| `setup-history.js` | Builds the practice repository `nkwen-price-list/` with 9 real commits. Run it once. |
| `FINDINGS.md` | **Your work.** Your investigation notes and answers. |

## Steps
1. In a terminal in this folder, run `node setup-history.js`, then `cd nkwen-price-list`.
2. Run `node --test` and read which test fails.
3. Investigate with `git log --oneline`, `git log -p prices.js`, `git blame prices.js` and `git show <hash>`.
4. Confirm with `git bisect` (see the lesson).
5. Fix it safely on a new branch, and run `node --test` again.
6. Fill in `FINDINGS.md`. Commit `FINDINGS.md` to **your own** repository (not inside `nkwen-price-list`).

Tip: `nkwen-price-list/` has its own `.git` folder. Add the line `nkwen-price-list/` to your repository's `.gitignore` so you do not commit a repository inside a repository.
