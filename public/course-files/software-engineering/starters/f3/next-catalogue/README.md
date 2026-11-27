# Starter: Next.js Essentials (Chapter 9, Frontend)

The Nkwen catalogue as a small **Next.js (App Router)** site. Pages are built on the server, so the HTML already contains the products.

```
npm install                  # large download: do it on Wi-Fi if you can
npm run dev                  # http://localhost:3000 while you work
npm run build && npm start   # the production server
npm test                     # in a SECOND terminal, while "npm start" runs
```

At the start, `npm test` shows **3 failed**, each with a hint.

| File | What it is |
|---|---|
| `app/layout.tsx` | Wraps every page (header, footer, `<html lang>`). Done. |
| `app/page.tsx` | The home page `/`. TODO 1-2. |
| `app/products/[id]/page.tsx` | One page per product, `/products/6`. TODO 3-5. |
| `components/AddToBasket.tsx` | A button: needs `'use client'`. TODO 6. |
| `app/not-found.tsx` | The 404 page. Done. |
| `lib/api.ts` | Reads the Nkwen course API on the server, cached for hours. Done. |
| `tests/pages.test.mjs` | Fetches your pages' HTML and checks it. |

To use another copy of the API, copy `.env.example` to `.env.local` and change `NKWEN_API_URL`.
