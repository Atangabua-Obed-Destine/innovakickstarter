#!/bin/sh
# Runs every time the server starts on the host.
set -e

# 0. TODO: run the production checklist first, so a dangerous setting stops the start:
#    php artisan nkwen:deploy-check

# 1. Build any new tables. Never "migrate:fresh" on a real database: it wipes everything.
php artisan migrate --force

# 2. Load the Nkwen data the first time only.
php artisan db:seed --class=SeedIfEmptySeeder --force

# 3. TODO: make sure the demo staff accounts exist (StaffSeeder, from the project starter).

# 4. Cache the settings and routes for speed.
php artisan config:cache
php artisan route:cache

# 5. Start the web server on the port the host gives us (Render uses PORT, default 10000).
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
