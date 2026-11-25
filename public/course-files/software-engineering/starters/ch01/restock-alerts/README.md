# Restock Alerts (Chapter 1 project)

Mrs. Enow Patience, manager of the Nkwen branch, wants a list every Monday morning: which products are fine, which are getting low, and which she must order now.

## The rule

For each product, compare its `quantity` with its `reorderLevel`:

| Status | When |
|---|---|
| `RESTOCK` | the quantity is **at or below** the reorder level |
| `LOW` | the quantity is above the reorder level, but **at or below twice** the reorder level |
| `OK` | the quantity is more than twice the reorder level |

Some quantities match **both** the RESTOCK rule and the LOW rule. They must say `RESTOCK`. This is the same lesson as FizzBuzz: check the most specific case first.

## Output format

One line per product, then one summary line. Example with made-up products:

```
Restock alerts for Nkwen (NKW)
OK - First example: 40 left (reorder level 10)
LOW - Second example: 15 left (reorder level 10)
RESTOCK - Third example: 2 left (reorder level 5)
Summary: 1 OK, 1 LOW, 1 RESTOCK
```

## Steps

1. Copy this folder into your repository as `ch01-first-day/restock-alerts/`.
2. Run `node --test`. Most checks fail: that is expected, because the code is not written yet.
3. Write `stockStatus()` in `restock.js`. Run `node --test` until the first four checks pass.
4. Write the loop in `printReport()`. Run `node restock.js` and read the output.
5. Check three products by hand against the rule.
6. Add the summary line. Run `node --test` until all six checks pass.
7. Commit and push with a clear message.
