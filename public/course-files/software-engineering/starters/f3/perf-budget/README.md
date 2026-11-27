# Starter: Performance on Low-End Phones & 3G (Chapter 9, Frontend)

Two files to add to **your shop** repository:

| File | What it is |
|---|---|
| `scripts/check-budget.mjs` | Checks your built site (`dist/`) against a performance budget: the first JavaScript, each image, all images. |
| `PERFORMANCE.md` | Your measurement log: before, what you changed, after. |

```
npm run build
node scripts/check-budget.mjs        # or add "budget": "node scripts/check-budget.mjs" to package.json
```

The budget (edit it at the top of the script only if your mentor agrees):
- entry JavaScript at most **100 KB** gzip (what servers really send);
- any one image at most **100 KB**;
- all images together at most **400 KB**.

Measure with Lighthouse in an InPrivate/Incognito window, **Mobile**, on the built site (`npm run build && npm run preview`), not on `npm run dev`.
