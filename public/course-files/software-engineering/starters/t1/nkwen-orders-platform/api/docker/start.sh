#!/bin/sh
# Runs every time the container starts (on Render, after every deploy and after every sleep).
set -e

# SQLite only: make the database file if it is missing.
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
  touch database/database.sqlite
fi

# Create any new tables. Unlike Chapter 7 we do NOT use "fresh": real orders must survive restarts.
php artisan migrate --force

# Add the demo data: products, branches, stock and the demo staff accounts.
# Your seeders must be safe to run many times: use firstOrCreate, so a restart adds
# missing rows but never duplicates them and never resets the stock.
if [ "${SEED_ON_START:-true}" = "true" ]; then
  php artisan db:seed --force
fi

# Cache the settings and routes for speed.
php artisan config:cache
php artisan route:cache

# Start the web server on the port the host gives us (Render sets PORT).
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
