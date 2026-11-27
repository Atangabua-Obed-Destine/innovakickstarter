#!/bin/sh
# Runs every time the server starts on the host.
set -e

# 1. B2 change: the data now lives in MySQL and must SURVIVE restarts.
#    "migrate" only adds tables and columns that are new. It never wipes data.
#    (B1 used "migrate:fresh", which wipes everything: never use it on a real database.)
php artisan migrate --force

# 2. Load the Nkwen products, branches and stock the first time only.
php artisan db:seed --class=SeedIfEmptySeeder --force

# 3. Cache the settings and routes for speed.
php artisan config:cache
php artisan route:cache

# 4. Start the web server on the port the host gives us (Render uses PORT, default 10000).
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
