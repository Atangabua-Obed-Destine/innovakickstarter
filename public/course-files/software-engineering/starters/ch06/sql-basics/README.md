# SQL Basics in the Browser

Practice for the lesson **SQL Basics in the Browser** (Chapter 6).

Put this folder in your repository as `ch06-data-and-teamwork/sql-basics/`.

## Files
| File | What it is |
|---|---|
| `queries.sql` | **Your work.** Two warm-ups and six questions, with a space for each query and its result. |
| `data/` | The three Nkwen tables as CSV files (the same tables the SQL sandbox loads). |
| `run-sql.js` | A backup: runs `queries.sql` on your computer with Node.js 22.13 or newer. |

## Steps
1. Open the SQL sandbox on the lesson page. Run `SELECT * FROM products LIMIT 5;` to check it works.
2. Write each query in the sandbox first. When it gives a sensible result, copy it into `queries.sql` with its result.
3. No sandbox (or offline)? Run `node run-sql.js` in this folder. It runs every query in `queries.sql`.
4. Commit and push.
