# Protecting Routes with Middleware (starter)

Copy into your Laravel project, keeping the folders:
- `app/Http/Middleware/EnsureStaffIsActive.php`: fill in the TODOs, then alias it as `active` in `bootstrap/app.php`.
- `tests/TestCase.php`: adds `actingAsStaff($role, $branch)` and `actingAsHeadOffice()` for your tests.
- `tests/Feature/ProtectedRoutesTest.php`: 6 self-checks.

After you protect the routes, older tests will fail with 401: log them in with `$this->actingAsHeadOffice();` in a `setUp()`.
