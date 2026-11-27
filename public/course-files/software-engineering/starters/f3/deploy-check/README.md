# Starter: Deploying a Frontend (Chapter 9, Frontend)

Files for **your shop** repository, and a checker for your live site.

| File | Use it for |
|---|---|
| `netlify.toml` | Netlify: build command, publish folder, and the single-page-app rewrite. |
| `public/_redirects` | Netlify or Cloudflare Pages: the same rewrite in one line. Use this **or** netlify.toml. |
| `vercel.json` | Vercel: the rewrite. |
| `.env.example` | The environment variable your shop needs (`VITE_API_URL`). Copy to `.env.local` on your laptop. |
| `check-live.mjs` | Checks the live site: HTTPS, `<html lang>`, viewport, deep links, no `localhost`, compression. |

```
node check-live.mjs https://YOUR-SITE.netlify.app products/6
```
Write the deep link without the first `/` (Git Bash on Windows changes `/products/6` into a Windows path).
