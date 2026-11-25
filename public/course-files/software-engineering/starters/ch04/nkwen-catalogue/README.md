# Project: product catalogue with search, filters and basket

**Brief from Mr. Asong Bertrand:** customers want to see what is in stock at their branch, search, filter by category, build a basket and come back later without losing it. Build the shop page. Use the parts you already built in this chapter. There is no server yet: the data is in `products.js`. In Chapter 5 it will come live from our API.

## Files
| File | What to do |
|---|---|
| `shop.html`, `shop.css` | Ready. Change the look if you like. |
| `products.js` | The real Nkwen Traders data (20 products, 5 branches). Do not edit. |
| `format.js`, `basket.js`, `storage.js` | **Replace each one** with your finished file from Changing the Page, Problem Solving 104 and Saving Data. |
| `catalogue.js` | New: write `filterProducts` (and the optional `sortProducts`). |
| `app.js` | New: the page code. 10 TODOs. |
| `*.test.js` | Self-checks. Run `node --test` in this folder. |

## User stories (the reviewer checks every one)
1. All 20 products load from `products.js` as cards. No product is typed into the HTML.
2. Typing in Search filters as I type, ignoring upper and lower case. Clearing the box shows all 20 again.
3. Choosing a category shows only that category. Search and category work together.
4. When nothing matches, I see a helpful message, not a blank space.
5. Each card shows the price in FCFA (`36,005 FCFA`) and the stock at my chosen branch. "Add" is disabled when the product is out of stock there.
6. I can add, increase, decrease and remove items. I can never have more than the branch stock, or more than 10 of one product. The page tells me when I reach the limit.
7. Each line shows quantity x unit price and the line total. The basket total and the item count in the header are always right.
8. After a refresh (F5), my basket and my branch are still there. Broken saved data does not crash the page: I get an empty basket.
9. When I change branch, the basket fits the new stock, and a message tells me exactly what changed.
10. With only the keyboard I can search, filter, add and change quantities, and the focus never gets lost. Messages appear in the `role="status"` area.
11. At 360px there is no sideways scroll. Lighthouse Accessibility (Mobile) is 90 or higher.
12. `node --test` shows every test passing. The shop is live on GitHub Pages, and my Chapter 3 website links to it.

## Steps
1. Copy this folder to `ch04-interactive/nkwen-shop/` in your `innova-software-engineering` repository.
2. Replace `format.js`, `basket.js` and `storage.js` with your own finished files. Run `node --test`.
3. Write `filterProducts` in `catalogue.js` until its tests pass.
4. Open `shop.html` and do the TODOs in `app.js` in order. Refresh after each one.
5. Test every user story. Add a "Shop online" link to your Chapter 3 website.
6. Commit and push. Open the live page on a phone if you can.

## Self-check
- [ ] `node --test` passes (the sortProducts stretch test may show as skipped).
- [ ] I ticked all 12 user stories on the live page.
- [ ] I can explain event delegation and why the page is drawn again from `state`.
- [ ] I can explain every line, including any line an AI tool suggested.
