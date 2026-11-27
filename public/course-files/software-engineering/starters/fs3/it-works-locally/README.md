# Starter: Problem Solving FS3, It Works Locally... (Chapter 9, Full-stack)

Last Friday a teammate deployed the shop and the API. On his laptop everything worked. Live, nothing did. Mr. Asong collected three tickets (see the lesson). The `bugpack/` folder holds the files exactly as they were that day.

You will **rehearse the incident on your laptop**: the built shop on `http://localhost:4173` (like the live site) and your API on `http://127.0.0.1:8000`. Two different addresses, like the real deploy.

| File | What it is |
|---|---|
| `bugpack/shop/src/api.js` | The shop's api.js that day. |
| `bugpack/shop/.env.production` | The shop's production settings that day. |
| `bugpack/api/config/cors.php` | The API's CORS settings that day. |
| `bugpack/api/env-rehearsal.txt` | Lines from the API's settings that day. |
| `check-fullstack.mjs` | The checker from "Deploying Front and Back". |
| `INCIDENTS.md` | Your incident report. Fill it in. |

**Do this on a new branch in both repositories** (`git switch -c rehearsal`), so you can throw it away afterwards.
