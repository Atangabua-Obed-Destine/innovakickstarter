# Problem Solving 109: Role-Based Access Control (starter)

Copy into your Laravel project, keeping the folders:
- `app/Policies/OrderPolicy.php`: four rules to write (they deny everything for now).
- `tests/Feature/RbacTest.php`: 8 self-checks, one per row of the permission table.

You also write: `worksAt()` on the User model, the gates in `AppServiceProvider`, the `Gate::authorize` calls
in the controllers, and `GET /api/staff`. Run `php artisan test --filter=RbacTest`.
