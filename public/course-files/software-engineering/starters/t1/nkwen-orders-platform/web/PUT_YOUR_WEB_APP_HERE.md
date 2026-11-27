# web/: the web shop (frontend)

The frontend (or full-stack) members put the React + TypeScript (Vite) project here, so that
`web/package.json` and `web/package-lock.json` exist. The CI job "Web shop (React)" then runs
`npm ci`, `npm test` and `npm run build` on every pull request.

- `npm test` must finish by itself in CI: use `"test": "vitest run"` in package.json.
- Read the API address from `import.meta.env.VITE_API_URL`, never a hard-coded URL.
- Copy `contract/consumers/web/orders.contract.test.ts` into `web/tests/` (see that file).

Usually you start from one member's Cart & Checkout or Production Shop project (Frontend
Chapters 8-9). Copy its files here (not `.git`, not `node_modules/`, not `dist/`), open a pull
request, then delete this file.
