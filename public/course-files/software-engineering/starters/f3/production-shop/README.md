# Starter: Project, Production Shop (Chapter 9, Frontend)

Your Chapter 8 shop (React + TypeScript + React Router + checkout), made production quality. These files go into **your shop repository**.

| File | What it is |
|---|---|
| `QUALITY.md` | Your quality report: targets, scores, tests, accessibility, user stories. Fill it in. |
| `scripts/check-budget.mjs` | The performance budget from this chapter. |
| `check-live.mjs` | The live-site checker from "Deploying a Frontend". |
| `.github/workflows/ci.yml` | GitHub Actions: runs your tests, build and budget on every push. |

## Targets (all measured on your LIVE site, Lighthouse Mobile)
- Performance **90 or more** on the home page and one product page.
- Accessibility **90 or more** on the home, product and basket pages.
- `npm test`: every test passes, at least **12 meaningful tests** of your own, including one axe test.
- `npm run build` and `node scripts/check-budget.mjs` pass.
- `node check-live.mjs <your URL> <a deep link>`: 8 of 8.

## User stories (tick each one in QUALITY.md, with how you checked it)
1. As a customer on a cheap phone with 3G, I can read the products and prices in FCFA within a few seconds.
2. As a customer, I see the stock badge for my branch: "In stock", "Only N left" or "Out of stock".
3. As a customer, I can search by name (any letter case) and see how many products match.
4. As a customer, I can open a product page from a link someone sent me on WhatsApp, and refresh it.
5. As a customer, I can add, remove and change quantities in my basket, and the total is right (VAT included).
6. As a customer, my basket is still there after a refresh or a dropped connection.
7. As a customer, I cannot add more than the branch has.
8. As a customer, I can check out with my name and MTN MoMo, Orange Money or cash, and I see clear errors.
9. As a customer, I cannot send the same order twice by tapping "Place order" again while it is sending.
10. As a customer, when the network fails, I see a clear message and can try again.
11. As a keyboard user, I can do everything without a mouse, and I can always see where the focus is.
12. As a screen-reader user, every button, field, image and error is announced with a clear name.
