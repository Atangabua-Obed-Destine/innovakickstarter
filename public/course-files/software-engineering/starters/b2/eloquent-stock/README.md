# Eloquent: Models, Migrations & Relationships (starter)

Copy these files into your Chapter 7 Laravel project (`nkwen-products-api`), keeping the folders.

Finished examples to read first:
- `database/migrations/2026_12_01_000001_create_stock_levels_table.php`
- `database/migrations/2026_12_01_000005_add_reorder_level_to_products_table.php`
- `app/Models/StockLevel.php`

Your TODOs:
- the migrations for `orders`, `order_lines` and `stock_movements`
- the relationships in `Order`, `OrderLine` and `StockMovement` (and in your own `Product` and `Branch`)
- `database/seeders/StockSeeder.php` (then add it to `DatabaseSeeder` after `BranchSeeder`)
- `app/Http/Controllers/StockController.php` and its route

Self-check: `php artisan test --filter=StockLevelsTest` (9 tests). They need MySQL: see steps 1 to 3 of the lesson.
