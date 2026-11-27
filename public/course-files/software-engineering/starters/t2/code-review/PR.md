# Pull request #17: Add wholesale discount and VAT to order totals

**Author:** Fru Emmanuel (backend) · **Branch:** `feature/wholesale-discount` → `main` · **Reviewer:** you

## What the business asked for (from Mr. Asong's ticket NK-212)
> Wholesale customers get **5% off** when one order has **10 or more bags of rice** (Rice 25kg or Rice 50kg).
> The total stays in whole FCFA. Receipts must still show the VAT that is inside the price.

## Fru's description
- Adds `isWholesale()` and applies the 5% discount in `orderTotal()`.
- Adds VAT so head office can see it on receipts.
- Sorts items by product id, because receipts list them that way.
- Tests: all green (`node --test` shows 3 passing).

## Files
- `pr-17.diff`: the change, as GitHub shows it in "Files changed".
- `pr-17/orders/`: the code AFTER the change, so you can run it.
