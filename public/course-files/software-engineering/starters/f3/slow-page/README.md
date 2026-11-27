# Starter: Problem Solving F3, The Slow Page (Chapter 9, Frontend)

This week's offers page. On Mr. Kenfack Roland's phone in Bafoussam it takes ages to appear, the page jumps while it loads, and it freezes for seconds.

```
npm install
npm run photos               # makes the 9 product photos on your computer (no download)
npm test                     # 8 tests: 4 fail at the start
npm run build && npm run budget   # the performance budget: fails at the start
npm run build && npm run preview  # measure THIS with Lighthouse (Mobile), not npm run dev
```

**Rules**
- Keep the page looking and reading the same. `dates.test.ts` already passes: keep it green.
- Do not change the tests or the budget.
- Measure before you change anything, and after each fix.
- `scripts/shrink-photos.mjs` is a half-written tool for one of the fixes. Finish it if you need it.

Fill in `FIXLOG.md` as you go.
