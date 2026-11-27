# Building Login & Sessions (starter)

Copy into your Laravel project, keeping the folders:
- `database/factories/UserFactory.php`: adds staff roles for tests, for example `User::factory()->cashier('NKW')`.
- `tests/Feature/LoginTest.php`: 6 self-checks (token login, one message for both failures, rate limit, logout, deactivated account, session login).

You write: `HasApiTokens` on the User model, `login`/`me`/`logout` in AuthController, the `login` rate limiter,
the session routes in `routes/web.php`, and the API routes. Run `php artisan test --filter=LoginTest`.
