# Project: Orders & Inventory API (starter)

Copy these files into your Laravel project (`nkwen-products-api`), keeping the folders.

- `tests/Feature/OrdersApiTest.php` and `tests/Feature/StockApiTest.php`: 16 self-checks for the contract.
- `contracts/b2/contract.json` and `contracts/b2/check-contract.mjs`: the 15 checks the platform runs on your live API.
  Run: `node contracts/b2/check-contract.mjs http://127.0.0.1:8000`
- `Dockerfile`: the Chapter 7 image plus PHP's MySQL driver (`pdo_mysql`).
- `docker/start.sh`: migrates without wiping data, then seeds an empty database once.
- `database/seeders/SeedIfEmptySeeder.php`: used by `start.sh`.

Read the lesson for the full contract and the rules.
