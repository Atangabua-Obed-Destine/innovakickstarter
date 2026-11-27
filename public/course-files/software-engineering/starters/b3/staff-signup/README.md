# Building Signup with Hashed Passwords (starter)

Copy into your Laravel project, keeping the folders:
- `database/migrations/2026_12_15_000001_add_staff_fields_to_users_table.php`: fill in the TODOs.
- `app/Http/Resources/StaffResource.php`: the public shape of a member of staff (finished).
- `tests/Feature/SignupTest.php`: 6 self-checks.

You write: the `User` model changes, `RegisterStaffRequest`, `AuthController::register()` and the route `POST /api/register`.
Run `php artisan test --filter=SignupTest`.
