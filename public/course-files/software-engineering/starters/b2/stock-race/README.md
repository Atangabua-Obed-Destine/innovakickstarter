# Transactions & Stock Integrity (starter)

Copy these files into your Laravel project, keeping the folders.

- `app/Services/PlaceOrder.php`: sells an order at a branch. It has TWO bugs. Fix the TODOs.
- `app/Exceptions/NotEnoughStock.php`: answers 409 with the numbers when stock is short (finished).
- `app/Console/Commands/SellOne.php`: one "cashier" (`php artisan nkwen:sell NKW 6 1`). The race test starts six at once.
- `config/nkwen.php`: the "slow till" setting that the race test uses.
- `tests/Feature/PlaceOrderTest.php` and `tests/Feature/StockRaceTest.php`: the self-checks.

Run: `php artisan test --filter="PlaceOrderTest|StockRaceTest"`. The race test needs MySQL, not SQLite.
Do not remove the `slowTill()` call: real tills are slow, and the test uses it to make the race easy to see.
