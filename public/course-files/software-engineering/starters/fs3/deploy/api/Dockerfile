# Runs the Nkwen API on a free Docker host such as Render, now with MySQL.
# Changed from Chapter 7: pdo_mysql is installed, and start.sh no longer wipes the database.
FROM php:8.3-cli

# Tools Composer needs, and the MySQL driver for PHP (php:8.3-cli has SQLite but not MySQL).
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip ca-certificates \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install only what production needs (no PHPUnit, no Faker).
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PHP_CLI_SERVER_WORKERS=4

EXPOSE 10000
CMD ["sh", "docker/start.sh"]
