# Project: Secure Multi-Role Staff API (starter)

Copy into your Laravel project, keeping the folders:
- `database/seeders/StaffSeeder.php`: the three demo staff accounts (training only: their passwords are public).
- `tests/Feature/SecureStaffApiTest.php`: two end-to-end self-checks with real logins.
- `contracts/b3/contract.json` and `contracts/b3/check-contract.mjs`: the 15 checks the platform runs on your live API.
  Run: `node contracts/b3/check-contract.mjs http://127.0.0.1:8000`

Demo accounts (password `Nkwen-Demo-2026`): headoffice@nkwen.test, manager.nkw@nkwen.test, cashier.nkw@nkwen.test.
