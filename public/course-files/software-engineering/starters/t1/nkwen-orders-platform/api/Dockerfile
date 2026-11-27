# The Orders API in a container. Render (or any Docker host) builds this file and runs it.
# It grows from the Chapter 7 Dockerfile: it adds the MySQL and PostgreSQL drivers, and it
# keeps the data between restarts (see docker/start.sh).
FROM php:8.3-cli

# Tools Composer needs, and the database drivers (PostgreSQL needs its client library first).
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Copy Composer from its official image.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install only what production needs (no PHPUnit, no Faker).
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PHP_CLI_SERVER_WORKERS=4

EXPOSE 8000
CMD ["sh", "docker/start.sh"]
