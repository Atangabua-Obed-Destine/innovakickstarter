#!/bin/sh
# Runs every time the server starts on the host.
set -e

# 1. Bring the tables up to date. Never "migrate:fresh" here: it would delete every customer and order.
php artisan migrate --force

# 2. Fill products, branches and stock only the first time (when the products table is empty).
php artisan db:seed --class=FirstDeploySeeder --force

# 3. Cache the settings and routes for speed. Change an environment variable? The host restarts, and this runs again.
php artisan config:cache
php artisan route:cache

# 4. Start the web server on the port the host gives us (Render uses PORT, default 10000).
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
