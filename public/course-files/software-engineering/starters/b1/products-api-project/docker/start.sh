#!/bin/sh
# Runs every time the server starts on the host.
set -e

# 1. Make an empty SQLite file, then build the tables and fill them with the Nkwen data.
#    "fresh" wipes the tables first, so the demo data is reset at every start.
touch database/database.sqlite
php artisan migrate:fresh --seed --force

# 2. Cache the settings and routes for speed.
php artisan config:cache
php artisan route:cache

# 3. Start the web server on the port the host gives us (Render uses PORT, default 10000).
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
