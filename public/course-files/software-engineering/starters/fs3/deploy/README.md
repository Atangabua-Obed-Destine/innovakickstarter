# Starter: Deploying Front and Back (Chapter 9, Full-stack)

| File | Goes in | What it is |
|---|---|---|
| `api/Dockerfile` | Laravel | Changed from Chapter 7: installs the MySQL driver (`pdo_mysql`). |
| `api/docker/start.sh` | Laravel | Changed: `migrate --force` (never `migrate:fresh`), seeds only an empty database. |
| `api/database/seeders/FirstDeploySeeder.php` | Laravel | Seeds only when there are no products yet. |
| `api/.dockerignore` | Laravel | Keeps `.env`, `vendor` and tests out of the image. |
| `shop/netlify.toml` and `shop/public/_redirects` | React | Netlify build settings and the single-page-app rewrite (use one of them). |
| `check-fullstack.mjs` | anywhere | Checks that the live shop and the live API can talk: CORS, preflight, the API address in the build. |

```
node check-fullstack.mjs https://YOUR-SHOP.netlify.app https://YOUR-API.onrender.com
```
