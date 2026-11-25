# Nkwen Price & Stock Calculator (Chapter 2 project)

A command-line tool for the shops. It prices a basket, and it reports each branch's week: sales, best seller and stock after sales.

## Files

| File | What it is | You |
|---|---|---|
| `nkwen.json` | products, prices, Monday-morning stock, branches | read |
| `sales.json` | every till record from Monday 23 to Sunday 29 November 2026 | read |
| `reorder-levels.json` | reorder level per product id | read |
| `pricing.js` | prices, discount, VAT, `priceBasket()` | write |
| `money.js` | `formatFcfa()` | write |
| `stock.js` | `stockStatus()`, `stockAfterSales()` | write |
| `sales.js` | `checkSale()`, `splitSales()` | write |
| `report.js` | `branchReport()` | write |
| `cli.js` | the commands (mostly done) | finish `printReport()` |
| `calculator.test.js` | self-checks | do not change |
| `my.test.js` | your own tests | write (at least 10) |

## The rules

- **Money:** whole francs. `lineTotal = price x quantity`. Baskets of **50,000 FCFA or more** get **10% off** (rounded). Shelf prices **include 19.25% VAT**; a receipt shows the VAT inside the total.
- **Stock status:** `RESTOCK` at or below the reorder level, `LOW` at or below twice it, otherwise `OK`.
- **Bad records:** a sale is skipped (and listed with a reason) when its branch is not open, its product does not exist, or its quantity is not a whole number of at least 1.
- **Stock never goes below 0.** If more was sold than we had, show `0 left` and say by how much it was oversold.

## Report layout

Made-up numbers, to show the layout only:

```
Weekly report - Example Branch (EXB)
Sales: 12 sales, 40 units, 123,456 FCFA
Best seller: Example product (10 units)
Restock now (2): Product A, Product B
Getting low (1): Product C
Oversold: Product A by 2 (check the stock count)
Stock after sales:
  RESTOCK - Product A: 0 left (reorder level 5)
  ...
```

`node cli.js report all` prints every open branch, then the company total and the skipped records.

## Steps

1. Copy this folder into your repository as `ch02-javascript/price-stock-calculator/`.
2. Run `node --test` and read the failures. That is your to-do list.
3. `pricing.js` and `money.js` (reuse your lessons). Then try `node cli.js price 6 2 12 10`.
4. `sales.js`, then `stock.js`, then `report.js`. Run `node --test` after each.
5. Finish `printReport()` in `cli.js`. Try `node cli.js report NKW` and `node cli.js report all`.
6. Write at least 10 tests of your own in `my.test.js`.
7. Check one number by hand. Commit and push.
