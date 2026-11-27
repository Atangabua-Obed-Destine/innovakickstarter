# Quality report: <your shop name>

Live URL:
Repository:
Host and free-plan limits (read on <date>):

## Lighthouse, Mobile, live site (middle of 3 runs)
| Page | Performance | Accessibility | LCP | TBT | CLS |
|---|---|---|---|---|---|
| Home | | | | | |
| Product (/products/6) | | | | | |
| Basket | | | | | |

## Checks
- `npm test`: ___ tests passed (list your test files)
- `node scripts/check-budget.mjs`: entry JS ___ KB
- `node check-live.mjs <url> products/6`: ___ of 8
- CI on GitHub Actions (link to a green run):

## Accessibility
- Keyboard walk (what I tried, what I fixed):
- Screen reader (NVDA, Narrator or TalkBack): what it read for the Add button, a stock badge and a form error:

## User stories
| # | Story | Done? | How I checked |
|---|---|---|---|
| 1 | Products readable within a few seconds on 3G | | |
| 2 | Stock badge for my branch | | |
| 3 | Search, any letter case | | |
| 4 | Product page from a link, refresh works | | |
| 5 | Basket: add, remove, quantities, total | | |
| 6 | Basket survives a refresh | | |
| 7 | Cannot add more than in stock | | |
| 8 | Checkout with clear errors | | |
| 9 | No double order | | |
| 10 | Network failure: message and retry | | |
| 11 | Keyboard only, visible focus | | |
| 12 | Screen reader names | | |
