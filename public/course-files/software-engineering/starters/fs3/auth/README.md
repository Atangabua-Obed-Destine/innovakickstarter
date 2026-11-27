# Starter: Authentication End to End (Chapter 9, Full-stack)

| File | Goes in | What it is |
|---|---|---|
| `api/tests/Feature/AuthTest.php` | Laravel | 10 tests: sign up, roles, login, /api/me, logout, 401, 403. |
| `api/tests/Feature/OrdersCreateTest.php` | Laravel | Replaces the last lesson's file: orders now need a logged-in cashier. |
| `api/database/seeders/StaffSeeder.php` | Laravel | 3 demo staff accounts (password `Nkwen-Demo-2026`). Training only. |
| `shop/src/auth.js` | React | Log in, sign up, who am I, log out. 3 TODOs. |

At the start, `php artisan test` shows **17 failed**.

Demo accounts (the same as the Backend pathway):
| Email | Role | Branch |
|---|---|---|
| `headoffice@nkwen.test` | head_office | all |
| `manager.nkw@nkwen.test` | branch_manager | NKW |
| `cashier.nkw@nkwen.test` | cashier | NKW |
