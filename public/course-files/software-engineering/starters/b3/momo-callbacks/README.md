# Payments: Mobile Money Callbacks (starter)

Copy into your Laravel project, keeping the folders. Finished files:
- `database/migrations/2026_12_15_000002_create_payments_table.php`, `app/Models/Payment.php`
- `app/Services/RequestPayment.php`: asks the customer to pay (simulated: no real API is called)
- `app/Exceptions/OrderNotAwaitingPayment.php`: 409 for cash or already-paid orders
- `app/Console/Commands/FakeMomoCallback.php`: plays the provider and signs callbacks
  `php artisan nkwen:fake-momo-callback <reference> <amount> [--status=FAILED] [--bad-signature] [--url=...]`

Your TODOs: `app/Http/Controllers/MomoCallbackController.php`, the route, `config/services.php` and the secret in `.env` and `phpunit.xml`.
Self-check: `php artisan test --filter=MomoCallbackTest` (10 tests).
