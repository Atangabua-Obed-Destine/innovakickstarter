# Starter: Staff Login, Calling a Protected API (Chapter 9, Frontend)

A small staff app (React + TypeScript + React Router): a login page and a stock page that only staff may see.
It talks to the practice API's staff endpoints, which have the same shape as the Backend pathway's API.

```
npm install
cp .env.example .env.local     # only if you use the local copy of the practice API
npm test                       # 7 tests: all fail at the start
npm run dev                    # http://localhost:5173
```

Demo accounts (training only, password `Nkwen-Demo-2026`):
| Email | Role | Branch |
|---|---|---|
| `manager.nkw@nkwen.test` | branch_manager | NKW |
| `cashier.nkw@nkwen.test` | cashier | NKW |
| `headoffice@nkwen.test` | head_office | all |

| File | What it is |
|---|---|
| `src/api.ts` | `apiFetch` and `ApiError` (keeps the HTTP status). Done. |
| `src/auth.tsx` | Who is logged in, the token, login and logout. TODO 1-2. |
| `src/RequireAuth.tsx` | The protected route. TODO 3. |
| `src/pages/LoginPage.tsx` | The login form. Done: read it. |
| `src/pages/StockPage.tsx` | The manager's stock page. TODO 4-5. |
| `src/staff.test.tsx` | 7 tests that describe the finished app. Do not change them. |

## Steps
1. Install, run the tests (7 failed).
2. Log in by hand with curl, see the token.
3. Choose where to keep the token.
4. `login()` (TODO 1).
5. Send the token (TODO 4).
6. The protected route (TODO 3).
7. 401 and 403 (TODO 5).
8. `logout()` (TODO 2).
9. Try every demo account in the browser.
